<?php
namespace App\Http\Controllers;
use App\Models\{AdvanceInstallment,Employee,EmployeeAdvance};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BranchAccess;
use App\Services\ApprovalNotifier;

class EmployeeAdvanceController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('show_advances')||auth()->user()->can('review_advances')||auth()->user()->can('store_advances'),403);
        $access = app(BranchAccess::class);
        $advances = $access->throughEmployee(EmployeeAdvance::query(), auth()->user())
            ->with(['employee.branch', 'installmentRecords'])
            ->latest()
            ->paginate(20);
        $employees=$access->employees(Employee::query(),auth()->user())->where('status','active')->orderBy('name')->get();return view('dashboard.advances.index',compact('advances','employees'));
    }
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('store_advances'),403);
        $data=$request->validate(['employee_id'=>'required|exists:employees,id','amount'=>'required|numeric|min:1|max:99999999','installments'=>'required|integer|between:1,36','first_deduction_date'=>'required|date|after_or_equal:today','reason'=>'required|string|max:500']);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());
        EmployeeAdvance::create($data+['installment_amount'=>round($data['amount']/$data['installments'],2),'balance'=>$data['amount'],'requested_at'=>today(),'requested_by'=>auth()->id(),'status'=>'pending']);
        $employee=Employee::findOrFail($data['employee_id']);app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_advances','سلفة بانتظار المراجعة','طلب سلفة جديد من '.$employee->name,route('advances.index'),auth()->id());
        return back()->with('success','تم تسجيل طلب السلفة وبانتظار الاعتماد.');
    }
    public function review(Request $request,EmployeeAdvance $advance)
    {
        abort_unless(auth()->user()->can('review_advances'),403);app(BranchAccess::class)->assertEmployee($advance->employee,auth()->user());abort_unless($advance->status==='pending',409);
        $data=$request->validate(['decision'=>'required|in:approved,rejected','notes'=>'nullable|string|max:1000']);
        DB::transaction(function()use($advance,$data){
            $advance->update(['status'=>$data['decision'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now()]);
            if($data['decision']==='approved'){
                $remaining=(float)$advance->amount;$date=$advance->first_deduction_date->copy();
                for($i=0;$i<$advance->installments;$i++){$amount=$i===$advance->installments-1?$remaining:min($remaining,(float)$advance->installment_amount);AdvanceInstallment::create(['employee_advance_id'=>$advance->id,'due_date'=>$date->copy()->addMonthsNoOverflow($i)->toDateString(),'amount'=>$amount,'status'=>'scheduled']);$remaining-=$amount;}
            }
        });
        return back()->with('success',$data['decision']==='approved'?'تم اعتماد السلفة وإنشاء جدول الاستقطاع.':'تم رفض طلب السلفة.');
    }
}
