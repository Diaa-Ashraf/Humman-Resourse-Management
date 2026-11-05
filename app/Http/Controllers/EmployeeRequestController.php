<?php
namespace App\Http\Controllers;

use App\Models\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\BranchAccess;
use App\Services\ApprovalNotifier;

class EmployeeRequestController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('review_employee_requests'),403);
        $requests=app(BranchAccess::class)->throughEmployee(EmployeeRequest::query(),auth()->user())->with(['employee.branch','reviewer'])->latest()->paginate(25);
        return view('dashboard.employee-requests.index',compact('requests'));
    }

    public function storeProfileUpdate(Request $request)
    {
        $employee=auth()->user()->employee;abort_unless(auth()->user()->can('employee_portal')&&$employee,403);
        if($employee->requests()->where('request_type','profile_update')->where('status','pending')->exists())return back()->withErrors(['profile_update'=>'لديك بالفعل طلب تحديث بيانات قيد المراجعة.']);
        $data=$request->validate(['name'=>'nullable|string|max:255','phone'=>'nullable|string|max:30','address'=>'nullable|string|max:2000','emergency_contact_name'=>'nullable|string|max:180','emergency_contact_phone'=>'nullable|string|max:30','reason'=>'nullable|string|max:1000']);
        $fields=array_intersect_key($data,array_flip(['name','phone','address','emergency_contact_name','emergency_contact_phone']));
        $fields=array_filter($fields,fn($value)=>$value!==null);
        if(!$fields)throw \Illuminate\Validation\ValidationException::withMessages(['name'=>'أدخل تعديلًا واحدًا على الأقل.']);
        if(filled($data['reason']??null))$fields['_reason']=$data['reason'];
        EmployeeRequest::create(['employee_id'=>$employee->id,'request_type'=>'profile_update','subject'=>'طلب تحديث بيانات الموظف','details'=>$fields,'status'=>'pending']);
        app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_employee_requests','طلب تحديث بيانات','طلب تحديث بيانات من '.$employee->name,route('employee-requests.index'),auth()->id());
        return back()->with('success','تم إرسال طلب تحديث البيانات للمراجعة.');
    }

    public function storeLetter(Request $request)
    {
        $employee=auth()->user()->employee;abort_unless(auth()->user()->can('employee_portal')&&$employee,403);
        $data=$request->validate(['letter_type'=>'required|in:employment,salary,experience,bank','copies'=>'required|integer|between:1,5','purpose'=>'nullable|string|max:500']);$data['language']='ar';
        $labels=['employment'=>'إفادة عمل','salary'=>'مفردات مرتب','experience'=>'شهادة خبرة','bank'=>'تعريف بنكي'];
        EmployeeRequest::create(['employee_id'=>$employee->id,'request_type'=>'letter','subject'=>$labels[$data['letter_type']],'details'=>$data,'status'=>'pending']);
        app(ApprovalNotifier::class)->branchApprovers($employee->branch_id,'review_employee_requests','طلب خطاب موظف','طلب '.$labels[$data['letter_type']].' من '.$employee->name,route('employee-requests.index'),auth()->id());
        return back()->with('success','تم تسجيل طلب الخطاب لمراجعته وتجهيزه.');
    }

    public function review(Request $request,EmployeeRequest $employeeRequest)
    {
        app(BranchAccess::class)->assertEmployee($employeeRequest->employee,auth()->user());
        abort_unless(auth()->user()->can('review_employee_requests'),403);abort_unless($employeeRequest->status==='pending',409);
        abort_if($employeeRequest->employee?->user_id===auth()->id(),403,'لا يمكن للموظف مراجعة طلبه الشخصي.');
        $data=$request->validate(['decision'=>'required|in:approved,rejected','review_notes'=>'nullable|string|max:2000|required_if:decision,rejected','resolution'=>'nullable|string|max:2000','file'=>'nullable|file|mimes:pdf,doc,docx|max:10240']);
        if($employeeRequest->request_type==='letter'&&$data['decision']==='approved'&&!$request->hasFile('file'))throw \Illuminate\Validation\ValidationException::withMessages(['file'=>'ارفع نسخة الخطاب المعتمد ليتمكن الموظف من تنزيلها.']);
        DB::transaction(function()use($employeeRequest,$data,$request){
            $locked=EmployeeRequest::whereKey($employeeRequest->id)->lockForUpdate()->firstOrFail();abort_unless($locked->status==='pending',409);
            if($data['decision']==='approved'&&$locked->request_type==='profile_update'){
                $allowed=['name','phone','address','emergency_contact_name','emergency_contact_phone'];$changes=array_intersect_key($locked->details??[],array_flip($allowed));
                $locked->employee->update($changes);$locked->employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'self_service_profile_update','changes'=>$changes]);
            }
            $path=$request->hasFile('file')?$request->file('file')->store('employee-request-resolutions'):null;
            $locked->update(['status'=>$data['decision'],'reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_notes'=>$data['review_notes']??null,'resolution'=>$data['resolution']??null,'resolution_file_path'=>$path,'resolution_original_filename'=>$request->file('file')?->getClientOriginalName()]);
        });
        return back()->with('success','تم حفظ قرار طلب الموظف.');
    }

    public function downloadResolution(EmployeeRequest $employeeRequest)
    {
        $employee=auth()->user()->employee;
        abort_unless(auth()->user()->can('employee_portal')&&$employee&&$employeeRequest->employee_id===$employee->id,403);
        abort_unless($employeeRequest->status==='approved'&&$employeeRequest->resolution_file_path&&Storage::exists($employeeRequest->resolution_file_path),404);
        return Storage::download($employeeRequest->resolution_file_path,$employeeRequest->resolution_original_filename??$employeeRequest->subject.'.pdf');
    }

}
