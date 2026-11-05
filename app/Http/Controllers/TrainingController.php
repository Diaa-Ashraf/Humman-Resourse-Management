<?php
namespace App\Http\Controllers;

use App\Models\{Branch,Employee, Section, TrainingCourse, TrainingEnrollment, TrainingNeed, TrainingPlan};
use Illuminate\Http\Request;
use App\Services\BranchAccess;

class TrainingController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('show_training'), 403);
        $access=app(BranchAccess::class);$branchIds=$access->branchIds(auth()->user());
        $courses = TrainingCourse::with(['plan','branch'])->withCount('enrollments')->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->where(fn($sub)=>$sub->whereNull('branch_id')->orWhereIn('branch_id',$branchIds)))->latest('starts_at')->paginate(12);
        $employees = $access->employees(Employee::query(),auth()->user())->where('status','active')->orderBy('name')->get();
        $activeCourses = TrainingCourse::whereIn('status',['planned','active'])->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->where(fn($sub)=>$sub->whereNull('branch_id')->orWhereIn('branch_id',$branchIds)))->orderBy('title')->get();
        $enrollments = $access->throughEmployee(TrainingEnrollment::query(),auth()->user())->with(['course','employee.branch'])->latest()->paginate(20);
        $plans=TrainingPlan::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$branchIds))->with(['branch','creator','approver','needs.employee','needs.section'])->withCount(['needs','courses'])->withSum('courses','cost')->latest('year')->paginate(10,['*'],'plans_page')->withQueryString();
        $activePlans=TrainingPlan::whereIn('status',['approved','draft'])->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$branchIds))->orderByDesc('year')->get();$branches=Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$branchIds))->orderBy('name')->get();$sections=Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$branchIds))->orderBy('name')->get();
        return view('dashboard.training.index', compact('courses','employees','activeCourses','enrollments','plans','activePlans','branches','sections'));
    }

    public function storeCourse(Request $request)
    {
        abort_unless(auth()->user()->can('store_training'), 403);
        $data = $request->validate([
            'title'=>'required|string|max:180', 'provider'=>'nullable|string|max:180', 'duration_hours'=>'required|integer|min:1|max:10000','training_plan_id'=>'nullable|exists:training_plans,id','branch_id'=>'nullable|exists:branches,id',
            'cost'=>'required|numeric|min:0|max:99999999', 'starts_at'=>'nullable|date', 'ends_at'=>'nullable|date|after_or_equal:starts_at',
            'status'=>'required|in:planned,active,completed,cancelled', 'description'=>'nullable|string|max:5000',
        ]);
        if(!empty($data['training_plan_id'])){\Illuminate\Support\Facades\DB::transaction(function()use(&$data){$plan=TrainingPlan::whereKey($data['training_plan_id'])->lockForUpdate()->firstOrFail();app(BranchAccess::class)->assertBranch($plan->branch_id,auth()->user());abort_unless($plan->status==='approved',409);if($plan->branch_id&&$data['branch_id']&&$plan->branch_id!=$data['branch_id'])throw \Illuminate\Validation\ValidationException::withMessages(['branch_id'=>'البرنامج يجب أن يلتزم بفرع الخطة.']);$data['branch_id']=$data['branch_id']??$plan->branch_id;app(BranchAccess::class)->assertBranch(isset($data['branch_id'])?(int)$data['branch_id']:null,auth()->user());$spent=(float)$plan->courses()->sum('cost');if($spent+(float)$data['cost']>(float)$plan->budget)throw \Illuminate\Validation\ValidationException::withMessages(['cost'=>'تكلفة البرنامج تتجاوز الميزانية المعتمدة للخطة.']);TrainingCourse::create($data);});}
        else {app(BranchAccess::class)->assertBranch(isset($data['branch_id'])?(int)$data['branch_id']:null,auth()->user());TrainingCourse::create($data);}
        return back()->with('success','تم إنشاء البرنامج التدريبي.');
    }

    public function enroll(Request $request)
    {
        abort_unless(auth()->user()->can('store_training'), 403);
        $data = $request->validate(['training_course_id'=>['required',\Illuminate\Validation\Rule::exists('training_courses','id')->whereIn('status',['planned','active'])],'employee_id'=>['required',\Illuminate\Validation\Rule::exists('employees','id')->where('status','active')]]);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());$course=TrainingCourse::findOrFail($data['training_course_id']);if($course->branch_id)app(BranchAccess::class)->assertBranch((int)$course->branch_id,auth()->user());
        TrainingEnrollment::firstOrCreate($data, ['status'=>'enrolled']);
        return back()->with('success','تم تسجيل الموظف في البرنامج.');
    }

    public function updateEnrollment(Request $request, TrainingEnrollment $enrollment)
    {
        app(BranchAccess::class)->assertEmployee($enrollment->employee,auth()->user());
        abort_unless(auth()->user()->can('update_training'), 403);
        $data = $request->validate(['status'=>'required|in:enrolled,completed,failed,withdrawn','score'=>'nullable|numeric|min:0|max:100','completed_at'=>'nullable|date|required_if:status,completed','attended_hours'=>'required|numeric|min:0','assessment_before'=>'nullable|numeric|between:0,100','assessment_after'=>'nullable|numeric|between:0,100','effectiveness_rating'=>'nullable|integer|between:1,5','feedback'=>'nullable|string|max:2000']);
        if((float)$data['attended_hours']>(float)$enrollment->course->duration_hours)throw \Illuminate\Validation\ValidationException::withMessages(['attended_hours'=>'ساعات الحضور لا تتجاوز مدة البرنامج.']);if($data['status']==='completed'&&(float)$data['attended_hours']<=0)throw \Illuminate\Validation\ValidationException::withMessages(['attended_hours'=>'أدخل ساعات الحضور قبل تسجيل إتمام البرنامج.']);
        $enrollment->update($data);
        return back()->with('success','تم تحديث سجل التدريب.');
    }

    public function storePlan(Request $request)
    {
        abort_unless(auth()->user()->can('store_training'),403);
        $data=$request->validate(['year'=>'required|integer|between:2020,2100','name'=>'required|string|max:180','branch_id'=>'nullable|exists:branches,id','budget'=>'required|numeric|min:0|max:999999999','notes'=>'nullable|string|max:3000']);
        app(BranchAccess::class)->assertBranch(isset($data['branch_id'])?(int)$data['branch_id']:null,auth()->user());
        TrainingPlan::create($data+['created_by'=>auth()->id(),'status'=>'draft']);return back()->with('success','تم إنشاء مسودة الخطة السنوية للتدريب.');
    }

    public function updatePlan(Request $request,TrainingPlan $plan)
    {
        app(BranchAccess::class)->assertBranch($plan->branch_id,auth()->user());
        abort_unless(auth()->user()->can('store_training'),403);abort_unless($plan->status==='draft',409);
        $data=$request->validate(['name'=>'required|string|max:180','branch_id'=>'nullable|exists:branches,id','budget'=>'required|numeric|min:0|max:999999999','notes'=>'nullable|string|max:3000']);
        app(BranchAccess::class)->assertBranch(isset($data['branch_id'])?(int)$data['branch_id']:null,auth()->user());
        if((float)$plan->courses()->sum('cost')>(float)$data['budget'])throw \Illuminate\Validation\ValidationException::withMessages(['budget'=>'الميزانية الجديدة أقل من التكاليف المسجلة للخطة.']);
        $plan->update($data);return back()->with('success','تم تحديث بيانات خطة التدريب.');
    }

    public function storeNeed(Request $request,TrainingPlan $plan)
    {
        app(BranchAccess::class)->assertBranch($plan->branch_id,auth()->user());
        abort_unless(auth()->user()->can('store_training'),403);abort_unless($plan->status==='draft',409);
        $data=$request->validate(['employee_id'=>'nullable|exists:employees,id','section_id'=>'nullable|exists:sections,id','skill_or_need'=>'required|string|max:180','priority'=>'required|in:low,normal,high,critical','rationale'=>'nullable|string|max:2000']);
        if(!$data['employee_id']&&!$data['section_id'])throw \Illuminate\Validation\ValidationException::withMessages(['section_id'=>'حدد موظفًا أو قسمًا لتوثيق الاحتياج.']);
        if($data['employee_id'])app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());if($data['section_id'])app(BranchAccess::class)->assertBranch((int)Section::findOrFail($data['section_id'])->branch_id,auth()->user());
        $plan->needs()->create($data);return back()->with('success','تم تسجيل احتياج التدريب في الخطة.');
    }

    public function storeNeedFromForm(Request $request)
    {
        abort_unless(auth()->user()->can('store_training'),403);
        $id=$request->validate(['training_plan_id'=>'required|exists:training_plans,id'])['training_plan_id'];
        return $this->storeNeed($request,TrainingPlan::findOrFail($id));
    }

    public function submitPlan(TrainingPlan $plan)
    {
        app(BranchAccess::class)->assertBranch($plan->branch_id,auth()->user());
        abort_unless(auth()->user()->can('store_training'),403);abort_unless($plan->status==='draft',409);abort_if($plan->needs()->doesntExist(),422,'أضف احتياجًا تدريبيًا واحدًا على الأقل قبل الإرسال.');
        $plan->update(['status'=>'submitted']);return back()->with('success','تم إرسال خطة التدريب للاعتماد.');
    }

    public function decidePlan(Request $request,TrainingPlan $plan)
    {
        app(BranchAccess::class)->assertBranch($plan->branch_id,auth()->user());
        abort_unless(auth()->user()->can('approve_training_plans'),403);abort_unless($plan->status==='submitted',409);
        abort_if($plan->created_by===auth()->id(),403,'يجب أن يعتمد الخطة مستخدم غير معدها.');
        $decision=$request->validate(['decision'=>'required|in:approved,returned','notes'=>'nullable|string|max:2000']);
        $plan->update(['status'=>$decision['decision']==='returned'?'draft':'approved','approved_by'=>$decision['decision']==='approved'?auth()->id():null,'approved_at'=>$decision['decision']==='approved'?now():null,'notes'=>$decision['notes']??$plan->notes]);
        return back()->with('success',$decision['decision']==='approved'?'تم اعتماد خطة التدريب.':'أعيدت الخطة للتعديل.');
    }
}
