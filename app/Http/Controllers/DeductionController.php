<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deduction;
use App\Models\Employee;
use App\Services\BranchAccess;
use App\Services\ApprovalNotifier;
class DeductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_deductions')->only(['index', 'show']);
        $this->middleware('permission:store_deductions')->only(['create', 'store']);
        $this->middleware('permission:update_deductions')->only(['edit', 'update']);
        $this->middleware('permission:delete_deductions')->only(['destroy']);
        $this->middleware('permission:review_deductions')->only(['review']);
    }
   

    public function index()
    {
        $deductions = app(BranchAccess::class)->throughEmployee(Deduction::query(),auth()->user())->with('employee')->latest('deduction_date')->paginate(20)->withQueryString();
        return view('dashboard.deductions.index', compact('deductions'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('store_deductions'), 403);
        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.deductions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where('status', 'active')],
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'deduction_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:255',
            'status' => 'required|in:applied',
        ]);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($validated['employee_id']),auth()->user());

        Deduction::create($validated);$employee=Employee::findOrFail($validated['employee_id']);
        app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_deductions','خصم بانتظار المراجعة','خصم جديد للموظف '.$employee->name,route('deductions.index'),auth()->id());

        return redirect()->route('deductions.index')->with('success', 'تم تسجيل الخصم للمراجعة والاعتماد.');
    }

    public function edit(Deduction $deduction)
    {
        app(BranchAccess::class)->assertEmployee($deduction->employee,auth()->user());
        abort_unless(auth()->user()->can('update_deductions'), 403);
        if ($deduction->status !== 'applied') {
            abort(403, 'لا يمكن تعديل خصم تمت مراجعته.');
        }

        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.deductions.edit', compact('deduction', 'employees'));
    }

    public function update(Request $request, Deduction $deduction)
    {
        app(BranchAccess::class)->assertEmployee($deduction->employee,auth()->user());
        if ($deduction->status !== 'applied') {
            abort(403, 'لا يمكن تعديل خصم تمت مراجعته.');
        }
        $validated = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where(fn($query) => $query->where('status', 'active')->orWhere('id', $deduction->employee_id))],
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'deduction_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:255',
            'status' => 'required|in:applied,rejected',
        ]);

        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($validated['employee_id']),auth()->user());$deduction->update($validated);

        return redirect()->route('deductions.index')->with('success', 'تم تحديث طلب الخصم.');
    }

    public function destroy(Deduction $deduction)
    {
        app(BranchAccess::class)->assertEmployee($deduction->employee,auth()->user());
        abort_unless(auth()->user()->can('delete_deductions'), 403);
        if ($deduction->status !== 'applied') {
            return redirect()->route('deductions.index')->with('error', 'لا يمكن حذف خصم تمت مراجعته.');
        }
        $deduction->delete();

        return redirect()->route('deductions.index')->with('success', 'تم حذف طلب الخصم.');
    }

    public function review(Request $request, Deduction $deduction)
    {
        app(BranchAccess::class)->assertEmployee($deduction->employee,auth()->user());
        abort_unless(auth()->user()->can('review_deductions'), 403);
        abort_if($deduction->status !== 'applied', 403, 'تمت مراجعة هذا الطلب مسبقًا.');
        $validated = $request->validate(['status' => 'required|in:approved,rejected']);
        $deduction->update(['status' => $validated['status']]);

        return redirect()->route('deductions.index')->with('success', $validated['status'] === 'approved'
            ? 'تم اعتماد الخصم وسيُحتسب عند إعداد راتب الموظف.'
            : 'تم رفض طلب الخصم.');
    }
}
