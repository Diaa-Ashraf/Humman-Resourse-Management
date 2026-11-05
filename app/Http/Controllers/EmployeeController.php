<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\EmployeeLifecycleTask;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\BranchAccess;
class EmployeeController extends Controller
{
    private const EMPLOYEE_LIMIT = 1000;
    public function __construct()
    {
        $this->middleware('permission:show_employees')->only(['index', 'show']);
        $this->middleware('permission:store_employees')->only(['create', 'store']);
        $this->middleware('permission:update_employees')->only(['edit', 'update']);
        $this->middleware('permission:delete_employees')->only(['destroy']);
    }

    // صفحة عرض كل الموظفين
    public function index(Request $request)
    {
        $access=app(BranchAccess::class);$user=auth()->user();
        $request->validate(['q'=>'nullable|string|max:100','branch_id'=>'nullable|exists:branches,id','section_id'=>'nullable|exists:sections,id','status'=>'nullable|in:active,inactive']);
        if($request->filled('branch_id'))$access->assertBranch((int)$request->branch_id,$user);
        $employees = $access->employees(Employee::query(),$user)->with(['section','shift','branch','manager'])
            ->when($request->filled('q'), fn($q) => $q->where(fn($sub) => $sub->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('employee_number','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')))
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id',$request->branch_id))
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id',$request->section_id))
            ->when($request->filled('status'), fn($q) => $q->where('status',$request->status))
            ->orderBy('name')->paginate(20)->withQueryString();
        $employeeCount = $access->employees(Employee::query(),$user)->count();
        $branches = $access->isGlobal($user)?Branch::orderBy('name')->get():Branch::whereIn('id',$access->branchIds($user))->orderBy('name')->get();
        $sections = Section::when(!$access->isGlobal($user),fn($q)=>$q->whereIn('branch_id',$access->branchIds($user)))->orderBy('name')->get();
        return view('dashboard.employees.index', compact('employees', 'employeeCount','branches','sections'));
    }

    // صفحة إنشاء موظف جديد
    public function create()
    {
        $access=app(BranchAccess::class);$user=auth()->user();
        abort_if(Employee::count() >= self::EMPLOYEE_LIMIT, 403, 'وصل النظام إلى الحد المحدد وهو 1000 موظف.');
        $sections = Section::where('is_active',true)->when(!$access->isGlobal($user),fn($q)=>$q->whereIn('branch_id',$access->branchIds($user)))->orderBy('name')->get();
        $shifts = Shift::where('status', 'open')->orderBy('start_time')->get();
        $branches = ($access->isGlobal($user)?Branch::where('is_active', true):Branch::whereIn('id',$access->branchIds($user)))->orderBy('name')->get();
        $managers = $access->employees(Employee::query(),$user)->where('status','active')->orderBy('name')->get();
        $users=User::with('employee')->orderBy('name')->get();
        return view('dashboard.employees.create', compact('sections', 'shifts', 'branches','managers','users'));
    }

    public function show(Employee $employee)
    {
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $employee->load(['branch','section.branch','shift','manager','directReports','documents','changeLogs.user','lifecycleTasks.assignee']);
        return view('dashboard.employees.show', compact('employee'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        if (Employee::count() >= self::EMPLOYEE_LIMIT) {
            throw ValidationException::withMessages(['employees' => 'وصل النظام إلى الحد المحدد وهو 1000 موظف.']);
        }
        $validated = $request->validated();

        app(BranchAccess::class)->assertBranch(isset($validated['branch_id'])?(int)$validated['branch_id']:null,auth()->user());
        if (!empty($validated['branch_id']) && Section::whereKey($validated['section_id'])->whereNotNull('branch_id')->where('branch_id','!=',$validated['branch_id'])->exists()) {
            throw ValidationException::withMessages(['section_id'=>'القسم المختار تابع لفرع مختلف.']);
        }

        if (empty($validated['branch_id'])) {
            unset($validated['branch_id']);
        }

        $employee = Employee::create($validated);
        $employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'created','changes'=>['employee'=>$employee->only(['name','employee_number','branch_id','section_id','position','hire_date'])]]);
        foreach (['استكمال ملف الموظف والمستندات المطلوبة','توقيع واستلام نسخة عقد العمل','تعريف الموظف بسياسات وإجراءات الشركة','تسليم الزي أو العهد الوظيفية','إضافة الموظف إلى جدول الوردية'] as $task) {
            $employee->lifecycleTasks()->create(['phase'=>'onboarding','title'=>$task]);
        }

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح.');
    }

    // صفحة تعديل الموظف
    public function edit(Employee $employee)
    {
        $access=app(BranchAccess::class);$user=auth()->user();$access->assertEmployee($employee,$user);
        $sections = Section::where(fn($q)=>$q->where('is_active',true)->orWhere('id',$employee->section_id))->when(!$access->isGlobal($user),fn($q)=>$q->whereIn('branch_id',$access->branchIds($user)))->orderBy('name')->get();
        $shifts = Shift::where(fn($query) => $query->where('status', 'open')->orWhere('id', $employee->shift_id))->orderBy('start_time')->get();
        $branches = Branch::where(fn($q)=>$access->isGlobal($user)?$q->where('is_active',true)->orWhere('id',$employee->branch_id):$q->whereIn('id',$access->branchIds($user))->orWhere('id',$employee->branch_id))->orderBy('name')->get();
        $managers = $access->employees(Employee::query(),$user)->where(fn($q)=>$q->where('status','active')->orWhere('id',$employee->manager_employee_id))->whereKeyNot($employee->id)->orderBy('name')->get();
        $users=User::with('employee')->orderBy('name')->get();
        return view('dashboard.employees.edit', compact('employee', 'sections', 'shifts', 'branches','managers','users'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $access=app(BranchAccess::class);$access->assertEmployee($employee,auth()->user());
        $validated = $request->validated();
        $access->assertBranch(isset($validated['branch_id'])?(int)$validated['branch_id']:null,auth()->user());

        if (!empty($validated['branch_id']) && Section::whereKey($validated['section_id'])->whereNotNull('branch_id')->where('branch_id','!=',$validated['branch_id'])->exists()) {
            throw ValidationException::withMessages(['section_id'=>'القسم المختار تابع لفرع مختلف.']);
        }
        $managerId=$validated['manager_employee_id']??null;
        $visited=[];
        while($managerId && !in_array($managerId,$visited,true)){
            if((int)$managerId===(int)$employee->id) throw ValidationException::withMessages(['manager_employee_id'=>'اختيار المدير يؤدي إلى تسلسل إداري دائري.']);
            $visited[]=$managerId;
            $managerId=Employee::whereKey($managerId)->value('manager_employee_id');
        }

        if (empty($validated['branch_id'])) {
            unset($validated['branch_id']);
        }

        $before = $employee->only(array_keys($validated));
        $employee->update($validated);
        $changes = [];
        foreach ($validated as $key=>$value) {
            if ((string)($before[$key] ?? '') !== (string)($value ?? '')) $changes[$key] = $key === 'base_salary' ? ['old'=>'[بيانات مالية محمية]','new'=>'[بيانات مالية محمية]'] : ['old'=>$before[$key] ?? null,'new'=>$value];
        }
        if ($changes) $employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'updated','changes'=>$changes]);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    public function destroy(Employee $employee)
    {
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        if ($employee->attendances()->exists() || $employee->salaries()->exists() || $employee->bonuses()->exists() || $employee->deductions()->exists() || $employee->leaves()->exists() || $employee->documents()->exists() || $employee->changeLogs()->exists() || $employee->lifecycleTasks()->exists()) {
            return back()->withErrors(['employee' => 'لا يمكن حذف موظف لديه سجلات موارد بشرية. غيّر حالته إلى غير نشط بدلاً من ذلك.']);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'تم حذف بيانات الموظف بنجاح.');
    }

    public function terminate(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->can('update_employees'),403);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $data=$request->validate(['termination_date'=>'required|date|after_or_equal:hire_date','termination_reason'=>'required|string|max:500']);
        $employee->update($data+['status'=>'inactive']);
        $employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'terminated','notes'=>$data['termination_reason'],'changes'=>$data]);
        foreach (['استلام العهد والأجهزة','إيقاف الصلاحيات والحسابات','تسوية الراتب والمستحقات النهائية','استلام بطاقة العمل أو الزي','إغلاق ملف الموظف وأرشفة مستنداته'] as $task) {
            $employee->lifecycleTasks()->firstOrCreate(['phase'=>'offboarding','title'=>$task]);
        }
        return back()->with('success','تم تسجيل إنهاء الخدمة وإنشاء قائمة مهام إخلاء الطرف.');
    }

    public function completeLifecycleTask(Request $request, EmployeeLifecycleTask $task)
    {
        abort_unless(auth()->user()->can('update_employees'),403);
        app(BranchAccess::class)->assertEmployee($task->employee,auth()->user());
        $data=$request->validate(['completed'=>'required|boolean']);
        $task->update(['completed_at'=>$data['completed'] ? now() : null,'completed_by'=>$data['completed'] ? auth()->id() : null]);
        return back()->with('success','تم تحديث مهمة دورة الموظف.');
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()->can('export_employees'),403);
        return response()->streamDownload(function(){
            $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['employee_number','name','email','phone','national_id','position','branch_code','section','shift','hire_date','employment_type','base_salary','status']);
            app(BranchAccess::class)->employees(Employee::with(['branch','section','shift']),auth()->user())->orderBy('name')->chunk(200,function($employees)use($out){foreach($employees as $e) fputcsv($out,[$e->employee_number,$e->name,$e->email,$e->phone,$e->national_id,$e->position,$e->branch?->code,$e->section?->name,$e->shift?->shift_name,$e->hire_date?->format('Y-m-d'),$e->employment_type,$e->base_salary,$e->status]);});
            fclose($out);
        },'employees-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function importTemplate(): StreamedResponse
    {
        abort_unless(auth()->user()->can('import_employees'),403);
        return response()->streamDownload(function(){
            $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['employee_number','name','email','phone','national_id','position','branch_code','section','shift','hire_date','employment_type','base_salary','status']);
            fclose($out);
        },'employee-import-template.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function importCsv(Request $request)
    {
        abort_unless(auth()->user()->can('import_employees'),403);
        $request->validate(['file'=>'required|file|mimes:csv,txt|max:10240']);
        if(Employee::count() >= self::EMPLOYEE_LIMIT) return back()->withErrors(['file'=>'وصل النظام إلى الحد الأقصى للموظفين.']);
        $handle=fopen($request->file('file')->getRealPath(),'r');
        $headers=fgetcsv($handle);
        if(!$headers){fclose($handle);return back()->withErrors(['file'=>'الملف فارغ أو غير صالح.']);}
        $headers=array_map(fn($h)=>trim(preg_replace('/^\xEF\xBB\xBF/','',(string)$h)),$headers);
        $required=['name','email','phone','position','section','shift','hire_date','base_salary'];
        if(array_diff($required,$headers)){fclose($handle);return back()->withErrors(['file'=>'الأعمدة المطلوبة: '.implode(', ',$required)]);}
        $created=0;$errors=[];$row=1;
        while(($values=fgetcsv($handle))!==false){$row++;if(count(array_filter($values,fn($v)=>trim((string)$v)!==''))===0)continue;
            $record=array_combine($headers,array_pad(array_slice($values,0,count($headers)),count($headers),''));
            if(Employee::count()+$created >= self::EMPLOYEE_LIMIT){$errors[]="السطر {$row}: تم بلوغ حد 1000 موظف.";break;}
            $validator=validator($record,['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:employees,email','phone'=>'required|string|max:30','position'=>'required|string|max:255','section'=>'required|string','shift'=>'required|string','hire_date'=>'required|date','base_salary'=>'required|numeric|min:0','employee_number'=>'nullable|string|max:40|unique:employees,employee_number','national_id'=>'nullable|string|max:40|unique:employees,national_id','branch_code'=>'nullable|string']);
            if($validator->fails()){$errors[]="السطر {$row}: ".$validator->errors()->first();continue;}
            $section=Section::where('name',$record['section'])->first();$shift=Shift::where('shift_name',$record['shift'])->where('status','open')->first();$branch=filled($record['branch_code']??null)?Branch::where('code',$record['branch_code'])->where('is_active',true)->first():null;
            if(!$section||!$shift||(!empty($record['branch_code'])&&!$branch)){$errors[]="السطر {$row}: القسم أو الوردية أو كود الفرع غير موجود/غير نشط.";continue;}
            if($branch&&$section->branch_id&&(int)$section->branch_id!==(int)$branch->id){$errors[]="السطر {$row}: القسم لا يتبع الفرع المحدد.";continue;}
            try{app(BranchAccess::class)->assertBranch($branch?->id,auth()->user());}catch(\Symfony\Component\HttpKernel\Exception\HttpException){$errors[]="السطر {$row}: الفرع خارج نطاق صلاحيتك.";continue;}
            try{DB::transaction(function()use($record,$section,$shift,$branch){$employee=Employee::create(['name'=>$record['name'],'email'=>$record['email'],'phone'=>$record['phone'],'position'=>$record['position'],'section_id'=>$section->id,'shift_id'=>$shift->id,'branch_id'=>$branch?->id,'hire_date'=>$record['hire_date'],'base_salary'=>$record['base_salary'],'employee_number'=>$record['employee_number']??null,'national_id'=>$record['national_id']??null,'employment_type'=>$record['employment_type']??'دوام كامل','status'=>$record['status']??'active']);$employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'imported']);foreach(['استكمال ملف الموظف والمستندات المطلوبة','توقيع واستلام نسخة عقد العمل','تعريف الموظف بسياسات وإجراءات الشركة','تسليم الزي أو العهد الوظيفية','إضافة الموظف إلى جدول الوردية'] as $task)$employee->lifecycleTasks()->create(['phase'=>'onboarding','title'=>$task]);});$created++;}catch(\Throwable $e){$errors[]="السطر {$row}: تعذر الحفظ بسبب تعارض بيانات.";}
        }
        fclose($handle);
        return back()->with('import_result',['created'=>$created,'errors'=>array_slice($errors,0,50),'error_count'=>count($errors)]);
    }
}
