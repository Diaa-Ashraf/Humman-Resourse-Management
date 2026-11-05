<?php
namespace App\Http\Controllers;
use App\Models\{Employee,LeaveBalance,LeaveBalanceTransaction,LeaveType};
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);
        $year=$request->validate(['year'=>'nullable|integer|between:2000,2100'])['year']??today()->year;
        $balances=LeaveBalance::with(['employee.branch','leaveType'])->where('year',$year)->when($request->filled('branch_id'),fn($q)=>$q->whereHas('employee',fn($e)=>$e->where('branch_id',$request->branch_id)))->orderBy('employee_id')->paginate(30)->withQueryString();
        $employees=Employee::where('status','active')->orderBy('name')->get();$types=LeaveType::where('status','active')->orderBy('name')->get();$branches=\App\Models\Branch::orderBy('name')->get();
        return view('dashboard.leave-balances.index',compact('balances','employees','types','branches','year'));
    }
    public function accrue(Request $request,LeaveBalanceService $service)
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);
        $year=$request->validate(['year'=>'required|integer|between:2000,2100'])['year'];
        $types=LeaveType::where('status','active')->where(fn($q)=>$q->whereNotNull('annual_entitlement_days')->orWhereNotNull('max_days'))->get();
        DB::transaction(function()use($types,$year,$service){Employee::where('status','active')->orderBy('id')->chunkById(100,function($employees)use($types,$year,$service){foreach($employees as $employee)foreach($types as $type)$service->balanceFor($employee,$type,(int)$year);});});
        return back()->with('success','تم احتساب الاستحقاق السنوي حتى الشهر الحالي للموظفين النشطين.');
    }
    public function adjust(Request $request)
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);
        $data=$request->validate(['employee_id'=>'required|exists:employees,id','leave_type_id'=>'required|exists:leave_types,id','year'=>'required|integer|between:2000,2100','days'=>'required|numeric|between:-365,365|not_in:0','notes'=>'required|string|max:500']);
        DB::transaction(function()use($data){$balance=LeaveBalance::firstOrCreate(['employee_id'=>$data['employee_id'],'leave_type_id'=>$data['leave_type_id'],'year'=>$data['year']]);if((float)$balance->available+(float)$data['days']<0)throw \Illuminate\Validation\ValidationException::withMessages(['days'=>'لا يمكن أن يصبح الرصيد المتاح سالبًا.']);$balance->increment('opening_balance',$data['days']);LeaveBalanceTransaction::create(['leave_balance_id'=>$balance->id,'type'=>'adjustment','days'=>$data['days'],'notes'=>$data['notes'],'created_by'=>auth()->id()]);});
        return back()->with('success','تم تسجيل تسوية الرصيد مع سببها في سجل الحركات.');
    }
}
