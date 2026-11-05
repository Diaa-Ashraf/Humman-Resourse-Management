<?php
namespace App\Http\Controllers;
use App\Models\{Employee,Shift,ShiftAssignment};
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BranchAccess;

class ShiftAssignmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('manage_shift_assignments'),403);
        $data=$request->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from','branch_id'=>'nullable|exists:branches,id']);
        $from=$data['from']??today()->startOfWeek()->toDateString();$to=$data['to']??today()->endOfWeek()->toDateString();
        $access=app(BranchAccess::class);if($request->filled('branch_id'))$access->assertBranch((int)$request->branch_id,auth()->user());
        $assignments=$access->throughEmployee(ShiftAssignment::query(),auth()->user())->with(['employee.branch','shift'])->whereBetween('work_date',[$from,$to])->when($request->filled('branch_id'),fn($q)=>$q->whereHas('employee',fn($e)=>$e->where('branch_id',$request->branch_id)))->orderBy('work_date')->orderBy('employee_id')->paginate(30)->withQueryString();
        $employees=$access->employees(Employee::query(),auth()->user())->with('branch')->where('status','active')->orderBy('name')->get();$shifts=Shift::where('status','open')->orderBy('start_time')->get();$branches=\App\Models\Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->orderBy('name')->get();
        return view('dashboard.shift-assignments.index',compact('assignments','employees','shifts','branches','from','to'));
    }
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('manage_shift_assignments'),403);
        $data=$request->validate(['employee_id'=>'required|exists:employees,id','shift_id'=>['required',\Illuminate\Validation\Rule::exists('shifts','id')->where('status','open')],'from'=>'required|date','to'=>'required|date|after_or_equal:from','weekdays'=>'required|array|min:1','weekdays.*'=>'integer|between:0,6','notes'=>'nullable|string|max:500']);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());
        if(CarbonPeriod::create($data['from'],$data['to'])->count()>62)return back()->withInput()->withErrors(['to'=>'يمكن إعداد جدول لمدة لا تتجاوز 62 يومًا في كل مرة.']);
        DB::transaction(function()use($data){foreach(CarbonPeriod::create($data['from'],$data['to']) as $day){if(!in_array($day->dayOfWeek,$data['weekdays']))continue;ShiftAssignment::updateOrCreate(['employee_id'=>$data['employee_id'],'work_date'=>$day->toDateString()],['shift_id'=>$data['shift_id'],'status'=>'scheduled','approved_by'=>auth()->id(),'notes'=>$data['notes']??null]);}});
        return back()->with('success','تم حفظ جدول الوردية للأيام المحددة.');
    }
    public function destroy(ShiftAssignment $assignment)
    {
        abort_unless(auth()->user()->can('manage_shift_assignments'),403);
        app(BranchAccess::class)->assertEmployee($assignment->employee,auth()->user());
        if(\App\Models\Attendance::where('employee_id',$assignment->employee_id)->whereDate('attendance_date',$assignment->work_date)->exists())return back()->withErrors(['assignment'=>'لا يمكن حذف تعيين وردية له سجل حضور؛ غيّر الوردية مع حفظ سجل الحضور.']);
        $assignment->delete();return back()->with('success','تم حذف تعيين الوردية.');
    }
}
