<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\BranchAccess;
use App\Services\ApprovalNotifier;
class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_leaves')->only(['index', 'show']);
        $this->middleware('permission:store_leaves')->only(['create', 'store']);
        $this->middleware('permission:update_leaves')->only(['edit', 'update']);
        $this->middleware('permission:delete_leaves')->only(['destroy']);
        $this->middleware('permission:review_leaves')->only(['review']);
    }

    public function index()
    {
        $leaves = app(BranchAccess::class)->throughEmployee(Leave::query(),auth()->user())->with('employee', 'leaveType')->latest('start_date')->paginate(20)->withQueryString();
        return view('dashboard.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        $types = LeaveType::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.leaves.create', compact('employees', 'types'));
    }

    public function store(Request $request,LeaveBalanceService $balances)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('status', 'active')],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|in:pending',
            'coverage_employee_id'=>['nullable','exists:employees,id',Rule::notIn([$request->input('employee_id')])],
        ]);
        $employee=Employee::findOrFail($validated['employee_id']);$type=LeaveType::findOrFail($validated['leave_type_id']);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $start=Carbon::parse($validated['start_date']);$end=Carbon::parse($validated['end_date']);
        $days=$balances->countDays($type,$employee,$start,$end);
        if($days<=0)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'الفترة المحددة لا تحتوي أيام إجازة محتسبة.']);
        if($type->requires_coverage&&!($validated['coverage_employee_id']??null))throw \Illuminate\Validation\ValidationException::withMessages(['coverage_employee_id'=>'هذا النوع من الإجازة يتطلب تحديد الموظف البديل.']);
        if($type->max_consecutive_days && $days>$type->max_consecutive_days)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'المدة تتجاوز الحد المتصل المسموح لهذا النوع.']);
        if(Leave::where('employee_id',$employee->id)->whereIn('status',['pending','approved'])->whereDate('start_date','<=',$end->toDateString())->whereDate('end_date','>=',$start->toDateString())->exists())throw \Illuminate\Validation\ValidationException::withMessages(['start_date'=>'تتداخل الفترة مع طلب إجازة قائم أو معتمد.']);
        $balances->assertAvailable($employee,$type,$start,$end);
        $leave=Leave::create($validated+['days_count'=>$days]);
        app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_leaves','طلب إجازة جديد','طلب إجازة جديد من '.$employee->name,route('leaves.index'),auth()->id());

        return redirect()->route('leaves.index')->with('success', 'تم إنشاء طلب الإجازة بنجاح');
    }

    public function show(Leave $leave)
    {
        app(BranchAccess::class)->assertEmployee($leave->employee,auth()->user());
        $leave->load('employee', 'leaveType');
        return view('dashboard.leaves.show', compact('leave'));
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('update_leaves'), 403);
        $leave = Leave::findOrFail($id);
        app(BranchAccess::class)->assertEmployee($leave->employee,auth()->user());
        if ($leave->status !== 'pending') {
            abort(403, 'لا يمكن تعديل طلب إجازة تمت مراجعته.');
        }
        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where(fn($q)=>$q->where('status','active')->orWhere('id',$leave->employee_id))->select('id', 'name')->orderBy('name')->get();
        $types = LeaveType::where(fn($query) => $query->where('status', 'active')->orWhere('id', $leave->leave_type_id))->select('id', 'name')->orderBy('name')->get();

        return view('dashboard.leaves.edit', compact('leave', 'employees', 'types'));
    }


    public function update(Request $request, Leave $leave,LeaveBalanceService $balances)
    {
        app(BranchAccess::class)->assertEmployee($leave->employee,auth()->user());
        if ($leave->status !== 'pending') {
            abort(403, 'لا يمكن تعديل طلب إجازة تمت مراجعته.');
        }
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where(fn($query) => $query->where('status', 'active')->orWhere('id', $leave->leave_type_id))],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|in:pending',
            'coverage_employee_id'=>['nullable','exists:employees,id',Rule::notIn([$request->input('employee_id')])],
        ]);
        $employee=Employee::findOrFail($validated['employee_id']);$type=LeaveType::findOrFail($validated['leave_type_id']);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $start=Carbon::parse($validated['start_date']);$end=Carbon::parse($validated['end_date']);$days=$balances->countDays($type,$employee,$start,$end);
        if($days<=0)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'الفترة المحددة لا تحتوي أيام إجازة محتسبة.']);
        if($type->requires_coverage&&!($validated['coverage_employee_id']??null))throw \Illuminate\Validation\ValidationException::withMessages(['coverage_employee_id'=>'هذا النوع من الإجازة يتطلب تحديد الموظف البديل.']);
        if($type->max_consecutive_days && $days>$type->max_consecutive_days)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'المدة تتجاوز الحد المتصل المسموح لهذا النوع.']);
        if(Leave::where('employee_id',$employee->id)->where('id','!=',$leave->id)->whereIn('status',['pending','approved'])->whereDate('start_date','<=',$end->toDateString())->whereDate('end_date','>=',$start->toDateString())->exists())throw \Illuminate\Validation\ValidationException::withMessages(['start_date'=>'تتداخل الفترة مع طلب إجازة قائم أو معتمد.']);
        $balances->assertAvailable($employee,$type,$start,$end,$leave->id);
        $leave->update($validated+['days_count'=>$days]);

        return redirect()->route('leaves.index')->with('success', 'تم تعديل طلب الإجازة بنجاح');
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('delete_leaves'), 403);
        $leave = Leave::findOrFail($id);
        app(BranchAccess::class)->assertEmployee($leave->employee,auth()->user());

        if ($leave->status !== 'pending') {
            return back()->withErrors(['msg' => 'لا يمكن حذف طلب إجازة تمت مراجعته.']);
        }

        $leave->delete();

        return redirect()->route('leaves.index')->with('success', 'تم حذف طلب الإجازة بنجاح');
    }

    public function review(Request $request, Leave $leave,LeaveBalanceService $balances)
    {
        abort_unless(auth()->user()->can('review_leaves'), 403);
        app(BranchAccess::class)->assertEmployee($leave->employee,auth()->user());
        abort_if($leave->status !== 'pending', 403, 'تمت مراجعة هذا الطلب مسبقًا.');
        $validated = $request->validate(['status' => 'required|in:approved,rejected','review_notes'=>'nullable|string|max:1000']);
        DB::transaction(function()use($leave,$validated,$balances){
            $locked=Leave::whereKey($leave->id)->lockForUpdate()->firstOrFail();abort_unless($locked->status==='pending',409);
            $locked->update(['status'=>$validated['status'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_notes'=>$validated['review_notes']??null]);
            if($validated['status']==='approved'){$segments=$balances->assertAvailable($locked->employee, $locked->leaveType, Carbon::parse($locked->start_date),Carbon::parse($locked->end_date),$locked->id);$balances->applyApproved($locked,$segments);}
        });

        return redirect()->route('leaves.index')->with('success', $validated['status'] === 'approved'
            ? 'تم اعتماد طلب الإجازة.'
            : 'تم رفض طلب الإجازة.');
    }
}
