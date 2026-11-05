<?php
namespace App\Http\Controllers;

use App\Models\{Employee, PayrollPolicy, PayrollRun, PayrollTaxBracket, Salary};
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\BranchAccess;
use Illuminate\Pagination\LengthAwarePaginator;

class PayrollController extends Controller
{
    private function recordEvent(PayrollRun $run,string $event,?string $from,?string $to,?string $notes=null,array $details=[]):void
    {
        DB::table('payroll_run_events')->insert(['payroll_run_id'=>$run->id,'user_id'=>auth()->id(),'event'=>$event,'from_status'=>$from,'to_status'=>$to,'notes'=>$notes,'details'=>json_encode($details,JSON_UNESCAPED_UNICODE),'created_at'=>now()]);
    }

    private function assertRunScope(PayrollRun $run): void
    {
        $access=app(BranchAccess::class);$scope=$run->scope_branch_ids;
        abort_unless($scope===null ? $access->isGlobal(auth()->user()) : ($access->isGlobal(auth()->user()) || !array_diff($scope,$access->branchIds(auth()->user()))),403,'المسير يتضمن فروعًا خارج نطاق صلاحيتك.');
    }

    public function index()
    {
        abort_unless(auth()->user()->can('show_salaries')||auth()->user()->can('review_payroll')||auth()->user()->can('approve_payroll')||auth()->user()->can('close_payroll'),403);
        $access=app(BranchAccess::class);$visible=PayrollRun::with(['creator','reviewer','approver'])->withCount('salaries')->withSum('salaries','total')->latest('period_end')->get()->filter(function($run)use($access){$scope=$run->scope_branch_ids;return $scope===null?$access->isGlobal(auth()->user()):($access->isGlobal(auth()->user())||!array_diff($scope,$access->branchIds(auth()->user())));});
        $page=LengthAwarePaginator::resolveCurrentPage();$runs=new LengthAwarePaginator($visible->forPage($page,15)->values(),$visible->count(),15,$page,['path'=>request()->url(),'query'=>request()->query()]);
        $policies=PayrollPolicy::where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',today()))->orderBy('key')->get();
        $taxBrackets=PayrollTaxBracket::where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',today()))->orderBy('sort_order')->get();
        $policyAudits=DB::table('payroll_policy_audits')->leftJoin('users','users.id','=','payroll_policy_audits.user_id')->select('payroll_policy_audits.*','users.name as user_name')->latest('payroll_policy_audits.created_at')->limit(20)->get();
        $missingRates=((float)($policies->firstWhere('key','insurance_employee_rate')?->value??0))<=0 || ((float)($policies->firstWhere('key','insurance_employer_rate')?->value??0))<=0 || $taxBrackets->isEmpty();
        return view('dashboard.payroll.index',compact('runs','policies','taxBrackets','missingRates','policyAudits'));
    }

    public function generate(Request $request)
    {
        abort_unless(auth()->user()->can('store_salaries'),403);
        $data=$request->validate(['period_start'=>'required|date_format:Y-m-d','period_end'=>'required|date_format:Y-m-d|after_or_equal:period_start','payment_date'=>'required|date_format:Y-m-d|after_or_equal:period_start']);
        $start=Carbon::parse($data['period_start'])->startOfDay();$end=Carbon::parse($data['period_end'])->endOfDay();
        if($start->day!==1||$end->format('Y-m-d')!==$start->copy()->endOfMonth()->format('Y-m-d'))throw ValidationException::withMessages(['period_start'=>'المسير يجب أن يغطي شهرًا ميلاديًا كاملًا.']);
        $access=app(BranchAccess::class);$scope=$access->isGlobal(auth()->user())?null:$access->branchIds(auth()->user());
        if($scope!==null&&!$scope)return back()->withErrors(['period_start'=>'لا توجد فروع محددة لنطاق حسابك.']);
        $existingRuns=PayrollRun::whereDate('period_start',$start)->whereDate('period_end',$end)->get();
        foreach($existingRuns as $existing){$existingScope=$existing->scope_branch_ids;if($existingScope===null||$scope===null||array_intersect($existingScope,$scope))return back()->withErrors(['period_start'=>'يوجد مسير للفترة نفسها يتداخل مع نطاق الفروع المحدد.']);}
        if(Salary::whereBetween('payment_date',[$start->toDateString(),$end->toDateString()])->whereNull('payroll_run_id')->exists())return back()->withErrors(['period_start'=>'توجد رواتب مسجلة خارج دورة المسير في هذا الشهر. راجعها أو سوّها قبل إنشاء المسير لتجنب إسقاط موظفين أو تكرار الصرف.']);

        $calculator=new PayrollCalculator($start,$end);
        DB::transaction(function()use($data,$start,$end,$calculator,$scope){
            $run=PayrollRun::create(['period_start'=>$start->toDateString(),'period_end'=>$end->toDateString(),'payment_date'=>$data['payment_date'],'status'=>'draft','created_by'=>auth()->id(),'scope_branch_ids'=>$scope]);
            $this->recordEvent($run,'created',null,'draft');
            Employee::where('status','active')->when($scope!==null,fn($q)=>$q->whereIn('branch_id',$scope))->with([
                'salaryComponents'=>fn($q)=>$q->whereDate('effective_from','<=',$end)->where(fn($sub)=>$sub->whereNull('effective_to')->orWhereDate('effective_to','>=',$start)),
                'salaryComponents.component',
                'bonuses'=>fn($q)=>$q->where('status','approved')->whereBetween('bonus_date',[$start->toDateString(),$end->toDateString()]),
                'deductions'=>fn($q)=>$q->where('status','approved')->whereBetween('deduction_date',[$start->toDateString(),$end->toDateString()]),
                'attendances'=>fn($q)=>$q->where('review_status','approved')->whereBetween('attendance_date',[$start->toDateString(),$end->toDateString()]),
                'attendances.shift',
                'advances'=>fn($q)=>$q->where('status','approved'),
                'advances.installmentRecords'=>fn($q)=>$q->where('status','scheduled')->whereBetween('due_date',[$start->toDateString(),$end->toDateString()]),
            ])->orderBy('id')->chunkById(100,function($employees)use($calculator,$run,$data){
                foreach($employees as $employee){
                    $values=$calculator->calculate($employee);
                    Salary::create($values+['employee_id'=>$employee->id,'payroll_run_id'=>$run->id,'payment_date'=>$data['payment_date'],'status'=>'pending']);
                }
            });
        });
        return redirect()->route('payroll.index')->with('success','تم إنشاء مسير شهري كمسودة. راجع القيم والسياسات قبل الإرسال.');
    }

    public function show(PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('show_salaries')||auth()->user()->can('review_payroll')||auth()->user()->can('approve_payroll')||auth()->user()->can('close_payroll'),403);
        $run->load(['creator','reviewer','approver','events.user']);
        $summary=$run->salaries()->selectRaw('COUNT(*) as employee_count, SUM(gross_amount) as gross_total, SUM(insurance_amount) as employee_insurance, SUM(employer_insurance_amount) as employer_insurance, SUM(tax_amount) as tax_total, SUM(total) as net_total')->first();
        $salaries=$run->salaries()->with(['employee.branch','employee.section'])->orderBy('employee_id')->paginate(50);
        return view('dashboard.payroll.show',compact('run','summary','salaries'));
    }

    public function recalculate(PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('store_salaries'),403);abort_unless($run->status==='draft',409);
        $start=$run->period_start->copy()->startOfDay();$end=$run->period_end->copy()->endOfDay();$calculator=new PayrollCalculator($start,$end);$count=0;
        DB::transaction(function()use($run,$start,$end,$calculator,&$count){
            $ids=$run->salaries()->pluck('employee_id');
            Employee::whereIn('id',$ids)->with([
                'salaryComponents'=>fn($q)=>$q->whereDate('effective_from','<=',$end)->where(fn($sub)=>$sub->whereNull('effective_to')->orWhereDate('effective_to','>=',$start)),
                'salaryComponents.component',
                'bonuses'=>fn($q)=>$q->where('status','approved')->whereBetween('bonus_date',[$start->toDateString(),$end->toDateString()]),
                'deductions'=>fn($q)=>$q->where('status','approved')->whereBetween('deduction_date',[$start->toDateString(),$end->toDateString()]),
                'attendances'=>fn($q)=>$q->where('review_status','approved')->whereBetween('attendance_date',[$start->toDateString(),$end->toDateString()]),
                'attendances.shift','advances'=>fn($q)=>$q->where('status','approved'),
                'advances.installmentRecords'=>fn($q)=>$q->where('status','scheduled')->whereBetween('due_date',[$start->toDateString(),$end->toDateString()]),
            ])->orderBy('id')->chunkById(100,function($employees)use($run,$calculator,&$count){
                foreach($employees as $employee){$salary=$run->salaries()->where('employee_id',$employee->id)->where('status','pending')->first();if(!$salary)continue;$salary->update($calculator->calculate($employee));$count++;}
            });
            $this->recordEvent($run,'recalculated','draft','draft',null,['employee_count'=>$count]);
        });
        return back()->with('success',"أعيد احتساب {$count} راتبًا وحُفظت نسخة الحساب الجديدة.");
    }

    public function submit(PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('store_salaries'),403);abort_unless($run->status==='draft',409);
        if(!$run->salaries()->exists())return back()->withErrors(['run'=>'المسير فارغ.']);
        if($run->salaries()->whereHas('employee',fn($q)=>$q->where('insurance_enabled',true))->exists() && ((float)(PayrollPolicy::where('key','insurance_employee_rate')->value('value')??0)<=0 || (float)(PayrollPolicy::where('key','insurance_employer_rate')->value('value')??0)<=0))
            return back()->withErrors(['run'=>'لا يمكن إرسال المسير قبل ضبط نسبتي اشتراك الموظف وجهة العمل في التأمينات.']);
        if($run->salaries()->whereHas('employee',fn($q)=>$q->where('tax_enabled',true))->exists() && !PayrollTaxBracket::whereNull('effective_to')->exists())
            return back()->withErrors(['run'=>'لا يمكن إرسال المسير قبل إدخال شرائح ضريبة المرتبات.']);
        $run->update(['status'=>'review_pending']);$this->recordEvent($run,'submitted','draft','review_pending');return back()->with('success','تم إرسال المسير للمراجعة.');
    }

    public function review(Request $request,PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('review_payroll'),403);abort_unless($run->status==='review_pending',409);
        abort_if($run->created_by===auth()->id(),403,'يجب أن تتم المراجعة بواسطة مستخدم غير مُعد المسير.');
        $data=$request->validate(['decision'=>'required|in:reviewed,returned','notes'=>'nullable|string|max:2000']);
        if($data['decision']==='returned'){$run->update(['status'=>'draft','reviewed_by'=>null,'reviewed_at'=>null,'notes'=>$data['notes']??null]);$this->recordEvent($run,'returned','review_pending','draft',$data['notes']??null);return back()->with('success','أعيد المسير للمراجعة والتعديل.');}
        $run->update(['status'=>'reviewed','reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'notes'=>$data['notes']??null]);$this->recordEvent($run,'reviewed','review_pending','reviewed',$data['notes']??null);return back()->with('success','تم تسجيل مراجعة المسير.');
    }

    public function approve(PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('approve_payroll'),403);abort_unless($run->status==='reviewed',409);
        abort_if($run->created_by===auth()->id()||$run->reviewed_by===auth()->id(),403,'يلزم أن يعتمد المسير مستخدم غير مُعده ومراجعه.');
        $run->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]);$this->recordEvent($run,'approved','reviewed','approved');return back()->with('success','تم اعتماد إجمالي المسير.');
    }

    public function close(PayrollRun $run)
    {
        $this->assertRunScope($run);
        abort_unless(auth()->user()->can('close_payroll'),403);abort_unless($run->status==='approved',409);
        $run->update(['status'=>'closed','closed_by'=>auth()->id(),'closed_at'=>now()]);$this->recordEvent($run,'closed','approved','closed');return back()->with('success','تم إقفال المسير وتثبيت نسخة الرواتب.');
    }

    public function updatePolicies(Request $request)
    {
        abort_unless(auth()->user()->can('manage_payroll_settings'),403);
        $request->merge(['brackets'=>array_values(array_filter($request->input('brackets',[]),fn($row)=>filled($row['lower_bound']??null)||filled($row['rate_percent']??null)))]);
        $data=$request->validate([
            'insurance_wage_floor'=>'required|numeric|min:0','insurance_wage_ceiling'=>'required|numeric|gt:insurance_wage_floor',
            'insurance_employee_rate'=>'required|numeric|between:0,100','insurance_employer_rate'=>'required|numeric|between:0,100',
            'tax_personal_allowance'=>'required|numeric|min:0','standard_work_days'=>'required|numeric|between:1,31',
            'standard_work_hours'=>'required|numeric|between:1,24','late_deduction_per_minute'=>'required|numeric|min:0',
            'brackets'=>'nullable|array','brackets.*.lower_bound'=>'required_with:brackets|numeric|min:0','brackets.*.upper_bound'=>'nullable|numeric|gt:brackets.*.lower_bound','brackets.*.rate_percent'=>'required_with:brackets|numeric|between:0,100',
        ]);
        $priorUpper=0;$brackets=array_values($data['brackets']??[]);$lastIndex=count($brackets)-1;
        foreach($brackets as $i=>$bracket){
            if(abs((float)$bracket['lower_bound']-$priorUpper)>0.001)throw ValidationException::withMessages(["brackets.{$i}.lower_bound"=>'يجب أن تبدأ الشرائح من صفر وتتصل دون فجوات أو تداخل.']);
            if($i<$lastIndex && $bracket['upper_bound']===null)throw ValidationException::withMessages(["brackets.{$i}.upper_bound"=>'اترك الحد الأعلى فارغًا للشريحة الأخيرة فقط.']);
            if($i===$lastIndex && $bracket['upper_bound']!==null)throw ValidationException::withMessages(["brackets.{$i}.upper_bound"=>'اترك الحد الأعلى فارغًا للشريحة الأخيرة.']);
            if($bracket['upper_bound']!==null){$priorUpper=(float)$bracket['upper_bound'];}
        }
        DB::transaction(function()use($data){
            $keys=['insurance_wage_floor','insurance_wage_ceiling','insurance_employee_rate','insurance_employer_rate','tax_personal_allowance','standard_work_days','standard_work_hours','late_deduction_per_minute'];
            $before=PayrollPolicy::whereIn('key',$keys)->whereDate('effective_from','<=',today())->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',today()))->orderBy('effective_from')->get()->groupBy('key')->map(fn($rows)=>$rows->last()->value)->all();
            $before['tax_brackets']=PayrollTaxBracket::whereNull('effective_to')->orderBy('sort_order')->get(['lower_bound','upper_bound','rate_percent','sort_order'])->toArray();
            foreach($keys as $key){
                $active=PayrollPolicy::where('key',$key)->whereNull('effective_to')->whereDate('effective_from','<',today())->get();
                foreach($active as $old)$old->update(['effective_to'=>today()->subDay()->toDateString()]);
                PayrollPolicy::updateOrCreate(['key'=>$key,'effective_from'=>today()->toDateString()],['label'=>match($key){'insurance_wage_floor'=>'حد أجر الاشتراك الأدنى','insurance_wage_ceiling'=>'حد أجر الاشتراك الأقصى','insurance_employee_rate'=>'نسبة اشتراك الموظف','insurance_employer_rate'=>'نسبة اشتراك جهة العمل','tax_personal_allowance'=>'الإعفاء الشخصي السنوي','standard_work_days'=>'أيام العمل القياسية بالشهر','standard_work_hours'=>'ساعات العمل القياسية باليوم',default=>'خصم الدقيقة المتأخرة'},'value'=>$data[$key],'updated_by'=>auth()->id()]);
            }
            PayrollTaxBracket::whereNull('effective_to')->update(['effective_to'=>today()->subDay()->toDateString()]);
            foreach($data['brackets']??[] as $i=>$bracket)PayrollTaxBracket::create($bracket+['sort_order'=>$i,'effective_from'=>today()->toDateString()]);
            $after=array_intersect_key($data,$before);
            $after['tax_brackets']=$data['brackets']??[];
            DB::table('payroll_policy_audits')->insert(['user_id'=>auth()->id(),'action'=>'settings_updated','before_values'=>json_encode($before,JSON_UNESCAPED_UNICODE),'after_values'=>json_encode($after,JSON_UNESCAPED_UNICODE),'created_at'=>now()]);
        });
        return back()->with('success','تم حفظ سياسات الرواتب والشرائح الضريبية بإصدار مؤرخ.');
    }
}
