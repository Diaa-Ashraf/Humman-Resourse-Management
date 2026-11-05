<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bonus;
use App\Models\Employee;
use App\Services\BranchAccess;
use App\Services\ApprovalNotifier;
class BonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_bonuses')->only(['index', 'show']);
        $this->middleware('permission:store_bonuses')->only(['create', 'store']);
        $this->middleware('permission:update_bonuses')->only(['edit', 'update']);
        $this->middleware('permission:delete_bonuses')->only(['destroy']);
        $this->middleware('permission:review_bonuses')->only(['review']);
    }
   

    public function index()
    {
        $bonuses = app(BranchAccess::class)->throughEmployee(Bonus::query(),auth()->user())->with('employee')->latest('bonus_date')->paginate(20)->withQueryString();
        return view('dashboard.bonuses.index', compact('bonuses'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('store_bonuses'), 403);
        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.bonuses.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where('status', 'active')],
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'bonus_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:255',
            'status' => 'required|in:applied',
        ]);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($validated['employee_id']),auth()->user());

         $bonus=Bonus::create($validated);$employee=Employee::findOrFail($validated['employee_id']);
        app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_bonuses','مكافأة بانتظار المراجعة','مكافأة جديدة للموظف '.$employee->name,route('bonuses.index'),auth()->id());
        return redirect()->route('bonuses.index')->with('success', 'تم تسجيل المكافأة للمراجعة والاعتماد.');
    }

    public function edit(Bonus $bonus)
    {
        app(BranchAccess::class)->assertEmployee($bonus->employee,auth()->user());
        abort_unless(auth()->user()->can('update_bonuses'), 403);
        if ($bonus->status !== 'applied') {
            abort(403, 'لا يمكن تعديل مكافأة تمت مراجعتها.');
        }

        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.bonuses.edit', compact('bonus', 'employees'));
    }

    public function update(Request $request, Bonus $bonus)
    {
        app(BranchAccess::class)->assertEmployee($bonus->employee,auth()->user());
        if ($bonus->status !== 'applied') {
            abort(403, 'لا يمكن تعديل مكافأة تمت مراجعتها.');
        }
        $validated = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where(fn($query) => $query->where('status', 'active')->orWhere('id', $bonus->employee_id))],
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'bonus_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:255',
            'status' => 'required|in:applied,rejected',
        ]);

        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($validated['employee_id']),auth()->user());$bonus->update($validated);
        return redirect()->route('bonuses.index')->with('success', 'تم تحديث طلب المكافأة.');
    }

    public function destroy(Bonus $bonus)
    {
        app(BranchAccess::class)->assertEmployee($bonus->employee,auth()->user());
        abort_unless(auth()->user()->can('delete_bonuses'), 403);
        if ($bonus->status !== 'applied') {
            return redirect()->route('bonuses.index')->with('error', 'لا يمكن حذف مكافأة تمت مراجعتها.');
        }

        $bonus->delete();
        return redirect()->route('bonuses.index')->with('success', 'تم حذف طلب المكافأة.');
    }

    public function review(Request $request, Bonus $bonus)
    {
        app(BranchAccess::class)->assertEmployee($bonus->employee,auth()->user());
        abort_unless(auth()->user()->can('review_bonuses'), 403);
        abort_if($bonus->status !== 'applied', 403, 'تمت مراجعة هذا الطلب مسبقًا.');
        $validated = $request->validate(['status' => 'required|in:approved,rejected']);
        $bonus->update(['status' => $validated['status']]);

        return redirect()->route('bonuses.index')->with('success', $validated['status'] === 'approved'
            ? 'تم اعتماد المكافأة وستدخل في حساب راتب الموظف.'
            : 'تم رفض طلب المكافأة.');
    }
}
