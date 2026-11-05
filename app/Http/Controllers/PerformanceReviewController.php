<?php
namespace App\Http\Controllers;

use App\Models\{Employee, PerformanceReview};
use Illuminate\Http\Request;
use App\Services\BranchAccess;

class PerformanceReviewController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('show_performance'), 403);
        $access=app(BranchAccess::class);$reviews = $access->throughEmployee(PerformanceReview::query(),auth()->user())->with(['employee.branch','employee.section','reviewer','approver','performanceGoals'])->latest('period_end')->paginate(20);
        $employees = $access->employees(Employee::query(),auth()->user())->with('branch')->where('status','active')->orderBy('name')->get();
        return view('dashboard.performance.index', compact('reviews','employees'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('store_performance'), 403);
        $goalsInput = array_values(array_filter($request->input('goals_list', []), fn($goal) => filled(trim($goal['title'] ?? ''))));
        $request->merge(['goals_list' => $goalsInput]);
        $data = $request->validate([
            'employee_id'=>'required|exists:employees,id', 'period_start'=>'required|date',
            'period_end'=>'required|date|after_or_equal:period_start', 'rating'=>'nullable|numeric|min:1|max:5',
            'strengths'=>'nullable|string|max:3000', 'improvement_areas'=>'nullable|string|max:3000', 'goals'=>'nullable|string|max:3000',
            'action'=>'required|in:draft,submitted',
            'employee_comments'=>'nullable|string|max:3000','goals_list'=>'nullable|array|max:20',
            'goals_list.*.title'=>'required|string|max:180','goals_list.*.measure_unit'=>'nullable|string|max:40',
            'goals_list.*.target_value'=>'nullable|numeric','goals_list.*.weight'=>'nullable|numeric|min:0',
        ]);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());
        $goals = $this->normalizeGoalWeights($data['goals_list'] ?? []);
        unset($data['goals_list']);

        if($data['action']==='submitted'&&!$goals&&!filled($data['goals']??null))throw \Illuminate\Validation\ValidationException::withMessages(['goals_list'=>'أضف هدفًا واحدًا قابلًا للقياس أو هدفاً عاماً على الأقل قبل الإرسال.']);
        if(PerformanceReview::where('employee_id',$data['employee_id'])->whereIn('status',['draft','submitted','approved'])->whereDate('period_start','<=',$data['period_end'])->whereDate('period_end','>=',$data['period_start'])->exists())throw \Illuminate\Validation\ValidationException::withMessages(['period_start'=>'توجد مراجعة أخرى تتداخل مع هذه الفترة.']);
        $data['reviewer_id'] = auth()->id();
        $data['status'] = $data['action'];
        $data['submitted_at']=$data['action']==='submitted'?now():null;
        unset($data['action']);
        $review=PerformanceReview::create($data);
        foreach($goals as $goal)$review->performanceGoals()->create($goal);
        return back()->with('success','تم حفظ تقييم الأداء بنجاح.');
    }

    public function update(Request $request,PerformanceReview $review)
    {
        app(BranchAccess::class)->assertEmployee($review->employee,auth()->user());
        abort_unless(auth()->user()->can('store_performance'),403);abort_unless(in_array($review->status,['draft','returned'],true),409);
        $request->merge(['goals_list'=>array_values(array_filter($request->input('goals_list',[]),fn($goal)=>filled(trim($goal['title']??''))))]);
        $data=$request->validate([
            'period_start'=>'required|date','period_end'=>'required|date|after_or_equal:period_start',
            'rating'=>'nullable|numeric|between:1,5','strengths'=>'nullable|string|max:3000',
            'improvement_areas'=>'nullable|string|max:3000','goals'=>'nullable|string|max:3000',
            'employee_comments'=>'nullable|string|max:3000','action'=>'required|in:draft,submitted',
            'goals_list'=>'nullable|array|max:20','goals_list.*.title'=>'required|string|max:180',
            'goals_list.*.measure_unit'=>'nullable|string|max:40',
            'goals_list.*.target_value'=>'nullable|numeric',
            'goals_list.*.weight'=>'nullable|numeric|min:0'
        ]);
        $goals=$this->normalizeGoalWeights($data['goals_list']??[]);
        unset($data['goals_list']);
        if($data['action']==='submitted'&&!$goals&&!filled($data['goals']??null))throw \Illuminate\Validation\ValidationException::withMessages(['goals_list'=>'أضف هدفًا واحدًا قابلًا للقياس على الأقل قبل الإرسال.']);
        if(PerformanceReview::where('employee_id',$review->employee_id)->whereKeyNot($review->id)->whereIn('status',['draft','submitted','approved'])->whereDate('period_start','<=',$data['period_end'])->whereDate('period_end','>=',$data['period_start'])->exists())throw \Illuminate\Validation\ValidationException::withMessages(['period_start'=>'توجد مراجعة أخرى تتداخل مع هذه الفترة.']);
        $action=$data['action'];unset($data['action']);$data['status']=$action;$data['submitted_at']=$action==='submitted'?now():null;$review->update($data);
        $review->performanceGoals()->delete();foreach($goals as $goal)$review->performanceGoals()->create($goal);
        return back()->with('success','تم تحديث المراجعة وأهدافها.');
    }

    /**
     * موازنة أوزان الأهداف تلقائياً لتساوي 100% دون إجبار المستخدم على حسابات معقدة
     */
    protected function normalizeGoalWeights(array $goals): array
    {
        if (empty($goals)) {
            return [];
        }

        $count = count($goals);
        $weights = array_map(fn($g) => isset($g['weight']) && is_numeric($g['weight']) ? max(0, (float)$g['weight']) : 0, $goals);
        $totalEntered = array_sum($weights);

        if ($totalEntered > 0) {
            if (abs($totalEntered - 100) < 0.001) {
                foreach ($goals as $i => &$g) {
                    $g['weight'] = (int) round($weights[$i]);
                }
                unset($g);
                return $goals;
            }

            $calculated = [];
            $runningSum = 0;
            foreach ($weights as $i => $w) {
                if ($i === $count - 1) {
                    $assigned = max(1, 100 - $runningSum);
                } else {
                    $assigned = max(1, (int) round(($w / $totalEntered) * 100));
                    $runningSum += $assigned;
                }
                $calculated[$i] = $assigned;
            }

            $diff = 100 - array_sum($calculated);
            if ($diff !== 0 && $count > 0) {
                $calculated[$count - 1] = max(1, $calculated[$count - 1] + $diff);
            }

            foreach ($goals as $i => &$g) {
                $g['weight'] = $calculated[$i];
            }
            unset($g);
        } else {
            $equal = intdiv(100, $count);
            $rem = 100 % $count;
            foreach ($goals as $i => &$g) {
                $g['weight'] = $equal + ($i === 0 ? $rem : 0);
            }
            unset($g);
        }

        return $goals;
    }

    public function decide(Request $request, PerformanceReview $review)
    {
        app(BranchAccess::class)->assertEmployee($review->employee,auth()->user());
        abort_unless(auth()->user()->can('review_performance'), 403);
        abort_unless($review->status === 'submitted', 409, 'هذا التقييم ليس بانتظار الاعتماد.');
        abort_if($review->reviewer_id===auth()->id() && !auth()->user()->hasRole('Super Admin'), 403, 'يجب أن يراجع التقييم مدير أو مراجع آخر.');
        abort_if($review->employee?->user_id===auth()->id(), 403, 'لا يمكن للموظف اعتماد تقييمه بنفسه.');
        $data = $request->validate(['status'=>'required|in:approved,returned','manager_comments'=>'nullable|string|max:3000','rating'=>'required_if:status,approved|nullable|numeric|between:1,5','goal_results'=>'nullable|array','goal_results.*.actual_value'=>'nullable|numeric','goal_results.*.manager_score'=>'nullable|numeric|between:0,100']);
        if($data['status']==='approved' && !$review->performanceGoals()->exists() && !$review->goals)abort(422,'أضف أهدافًا قابلة للقياس قبل اعتماد المراجعة.');
        foreach($data['goal_results']??[] as $goalId=>$result){$goal=$review->performanceGoals()->findOrFail($goalId);$goal->update($result);}
        $review->update(['status'=>$data['status'],'rating'=>$data['rating']??$review->rating,'manager_comments'=>$data['manager_comments']??null,'approved_by'=>$data['status']==='approved'?auth()->id():null]);
        return back()->with('success','تم تسجيل قرار التقييم.');
    }

}
