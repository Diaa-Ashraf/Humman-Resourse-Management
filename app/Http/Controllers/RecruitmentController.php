<?php
namespace App\Http\Controllers;

use App\Models\{Branch, Candidate, JobOpening, Section};
use App\Models\{CandidateInterview, Employee, Shift};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('show_recruitment'), 403);
        $openings = JobOpening::with(['branch','section'])->withCount('candidates')->latest()->paginate(10, ['*'], 'openings_page')->withQueryString();
        $candidates = Candidate::with(['opening','interviews.interviewer','hiredEmployee'])->when($request->filled('status'), fn($q) => $q->where('status', $request->status))->latest()->paginate(15, ['*'], 'candidates_page')->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $sections = Section::orderBy('name')->get();
        $activeOpenings = JobOpening::where('status','open')->where('approval_status','approved')->orderBy('title')->get();
        $interviews=CandidateInterview::with(['candidate','interviewer'])->whereHas('candidate',fn($q)=>$q->whereIn('status',['interview','offer']))->latest('scheduled_at')->limit(50)->get();
        $shifts=Shift::where('status','open')->orderBy('start_time')->get();
        return view('dashboard.recruitment.index', compact('openings','candidates','branches','sections','activeOpenings','interviews','shifts'));
    }

    public function storeOpening(Request $request)
    {
        abort_unless(auth()->user()->can('store_recruitment'), 403);
        $data = $request->validate([
            'title' => 'required|string|max:180', 'branch_id' => 'nullable|exists:branches,id',
            'section_id' => 'nullable|exists:sections,id', 'headcount' => 'required|integer|min:1|max:500',
            'employment_type' => 'required|string|max:50', 'closing_date' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string|max:5000',
        ]);
        if(!empty($data['branch_id'])&&!empty($data['section_id'])&&Section::whereKey($data['section_id'])->whereNotNull('branch_id')->where('branch_id','!=',$data['branch_id'])->exists())throw \Illuminate\Validation\ValidationException::withMessages(['section_id'=>'القسم المختار تابع لفرع مختلف.']);
        JobOpening::create($data+['approval_status'=>'pending','status'=>'paused','requested_by'=>auth()->id()]);
        return back()->with('success','تم إرسال طلب الاحتياج الوظيفي للاعتماد.');
    }

    public function decideOpening(Request $request,JobOpening $opening)
    {
        abort_unless(auth()->user()->can('approve_recruitment'),403);abort_unless($opening->approval_status==='pending',409);
        abort_if($opening->requested_by===auth()->id() && !auth()->user()->hasRole('Super Admin'), 403, 'يجب أن يعتمد طلب الاحتياج مستخدم آخر غير مقدم الطلب.');
        $data=$request->validate(['decision'=>'required|in:approved,returned','notes'=>'nullable|string|max:1000']);
        $approved=$data['decision']==='approved';$opening->update(['approval_status'=>$approved?'approved':'returned','status'=>$approved?'open':'paused','approved_by'=>auth()->id(),'approved_at'=>now(),'approval_notes'=>$data['notes']??null]);
        return back()->with('success',$approved?'تم اعتماد الاحتياج وفتح الشاغر.':'أعيد طلب الاحتياج لمقدم الطلب.');
    }

    public function updateOpening(Request $request,JobOpening $opening)
    {
        abort_unless(auth()->user()->can('store_recruitment'),403);abort_unless($opening->approval_status==='returned',409);
        $data=$request->validate(['title'=>'required|string|max:180','branch_id'=>'nullable|exists:branches,id','section_id'=>'nullable|exists:sections,id','headcount'=>'required|integer|min:1|max:500','employment_type'=>'required|string|max:50','closing_date'=>'nullable|date|after_or_equal:today','description'=>'nullable|string|max:5000']);
        if(!empty($data['branch_id'])&&!empty($data['section_id'])&&Section::whereKey($data['section_id'])->whereNotNull('branch_id')->where('branch_id','!=',$data['branch_id'])->exists())throw \Illuminate\Validation\ValidationException::withMessages(['section_id'=>'القسم المختار تابع لفرع مختلف.']);
        $opening->update($data+['approval_status'=>'pending','status'=>'paused','requested_by'=>auth()->id(),'approved_by'=>null,'approved_at'=>null,'approval_notes'=>null]);
        return back()->with('success','تم تحديث الطلب وإرساله للاعتماد من جديد.');
    }

    public function storeCandidate(Request $request)
    {
        abort_unless(auth()->user()->can('store_recruitment'), 403);
        $data = $request->validate([
            'job_opening_id' => ['nullable',Rule::exists('job_openings','id')->where(fn($q)=>$q->where('status','open')->where('approval_status','approved'))], 'name' => 'required|string|max:180',
            'email' => 'nullable|email|max:190', 'phone' => 'nullable|string|max:30',
            'source' => 'nullable|string|max:100', 'notes' => 'nullable|string|max:3000',
        ]);
        Candidate::create($data);
        return back()->with('success','تمت إضافة المرشح إلى قاعدة المواهب.');
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        abort_unless(auth()->user()->can('update_recruitment'), 403);
        $data = $request->validate(['status' => 'required|in:new,screening,interview,offer,rejected']);
        abort_if(in_array($candidate->offer_status,['sent','accepted'],true),409,'حدّث مرحلة العرض من قرار المرشح بدلًا من تغيير مساره يدويًا.');
        if($data['status']==='offer'&&!$candidate->interviews()->where('decision','passed')->exists())throw \Illuminate\Validation\ValidationException::withMessages(['status'=>'يلزم اجتياز مقابلة مسجلة قبل إصدار عرض وظيفي.']);
        $candidate->update($data);
        return back()->with('success','تم تحديث مرحلة المرشح.');
    }

    public function scheduleInterview(Request $request,Candidate $candidate)
    {
        abort_unless(auth()->user()->can('store_recruitment'),403);abort_if(in_array($candidate->status,['hired','rejected'],true),409);
        $data=$request->validate(['scheduled_at'=>'required|date|after_or_equal:now','interview_type'=>'required|string|max:50']);
        $candidate->interviews()->create($data+['interviewer_id'=>auth()->id(),'decision'=>'pending']);$candidate->update(['status'=>'interview']);
        return back()->with('success','تم جدولة المقابلة وتسجيل المرشح في مرحلتها.');
    }

    public function evaluateInterview(Request $request,CandidateInterview $interview)
    {
        abort_unless(auth()->user()->can('update_recruitment'),403);
        $data=$request->validate(['rating'=>'required|numeric|between:1,5','evaluation'=>'required|string|max:3000','decision'=>'required|in:passed,failed,pending']);
        $interview->update($data);if($data['decision']==='passed')$interview->candidate->update(['status'=>'offer']);return back()->with('success','تم حفظ تقييم المقابلة.');
    }

    public function sendOffer(Request $request,Candidate $candidate)
    {
        abort_unless(auth()->user()->can('update_recruitment'),403);abort_unless($candidate->status==='offer'&&$candidate->interviews()->where('decision','passed')->exists(),409);
        $data=$request->validate(['offer_salary'=>'required|numeric|min:0.01|max:99999999','offer_response_date'=>'required|date|after_or_equal:today']);
        $candidate->update(['offer_salary'=>$data['offer_salary'],'offer_status'=>'sent','offer_sent_at'=>now(),'offer_response_date'=>$data['offer_response_date'],'offer_decided_at'=>null]);
        return back()->with('success','تم تسجيل العرض الوظيفي المرسل للمرشح.');
    }

    public function decideOffer(Request $request,Candidate $candidate)
    {
        abort_unless(auth()->user()->can('update_recruitment'),403);abort_unless($candidate->offer_status==='sent',409);
        $decision=$request->validate(['decision'=>'required|in:accepted,declined'])['decision'];
        $candidate->update(['offer_status'=>$decision,'offer_decided_at'=>now()]);
        return back()->with('success',$decision==='accepted'?'تم قبول العرض؛ يمكن تحويل المرشح إلى ملف موظف.':'تم تسجيل رفض العرض.');
    }

    public function convertCandidate(Request $request,Candidate $candidate)
    {
        abort_unless(auth()->user()->can('store_employees'),403);abort_unless($candidate->offer_status==='accepted'&&!$candidate->hired_employee_id,409);
        if(Employee::count()>=1000)return back()->withErrors(['candidate'=>'وصل النظام إلى الحد الأقصى للموظفين.']);
        if($candidate->opening && $candidate->opening->candidates()->where('status','hired')->count()>=$candidate->opening->headcount)return back()->withErrors(['candidate'=>'تم استكمال العدد المطلوب لهذا الشاغر.']);
        $data=$request->validate(['email'=>'required|email|max:255|unique:employees,email','phone'=>'required|string|max:15','employee_number'=>'required|string|max:40|unique:employees,employee_number','national_id'=>'nullable|string|max:40|unique:employees,national_id','section_id'=>'required|exists:sections,id','shift_id'=>['required',Rule::exists('shifts','id')->where('status','open')],'hire_date'=>'required|date','date_of_birth'=>'nullable|date|before:today','gender'=>'nullable|in:ذكر,أنثى']);
        $employee=DB::transaction(function()use($candidate,$data){
            $opening=$candidate->opening;$section=Section::findOrFail($data['section_id']);
            if($opening?->branch_id && $section->branch_id && (int)$opening->branch_id!==(int)$section->branch_id)throw \Illuminate\Validation\ValidationException::withMessages(['section_id'=>'القسم لا يتبع فرع الشاغر.']);
            $employee=Employee::create($data+['name'=>$candidate->name,'position'=>$opening?->title??'موظف','branch_id'=>$opening?->branch_id,'employment_type'=>$opening?->employment_type??'دوام كامل','base_salary'=>$candidate->offer_salary,'status'=>'active']);
            $employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'hired_from_recruitment','notes'=>'تحويل من المرشح #'.$candidate->id]);
            foreach(['استكمال ملف الموظف والمستندات المطلوبة','توقيع واستلام نسخة عقد العمل','تعريف الموظف بسياسات وإجراءات الشركة','تسليم الزي أو العهد الوظيفية','إضافة الموظف إلى جدول الوردية'] as $task)$employee->lifecycleTasks()->create(['phase'=>'onboarding','title'=>$task]);
            $candidate->update(['status'=>'hired','hired_employee_id'=>$employee->id]);return $employee;
        });
        return back()->with('success','تم تحويل المرشح إلى ملف موظف رقم '.$employee->employee_number.'.');
    }

    public function destroyOpening(JobOpening $opening)
    {
        abort_unless(auth()->user()->can('delete_recruitment'), 403);
        if ($opening->candidates()->exists()) return back()->withErrors(['opening'=>'لا يمكن حذف شاغر مرتبط بمرشحين. أغلقه بدلًا من ذلك.']);
        $opening->delete();
        return back()->with('success','تم حذف الشاغر.');
    }
}
