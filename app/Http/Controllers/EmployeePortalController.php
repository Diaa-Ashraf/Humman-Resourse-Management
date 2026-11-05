<?php
namespace App\Http\Controllers;

use App\Models\{Branch, Employee, EmployeeRequest, InternalAnnouncement, Leave, LeaveType, PerformanceReview, Section, Shift};
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    private function resolveEmployee(bool $strict = true): ?Employee
    {
        abort_unless(auth()->user()->can('employee_portal'), 403);
        $user = auth()->user();
        $employee = $user->employee;

        // دعم السوبر أدمن: ضمان وجود وتفعيل ملف موظف نشط تلقائياً
        if ($user->hasRole('Super Admin')) {
            if (!$employee) {
                $mainBranch = Branch::first();
                $mainSection = Section::first();
                $mainShift = Shift::first();

                $employee = Employee::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => '01000000000',
                    'position' => 'المدير العام (Super Admin)',
                    'section_id' => $mainSection?->id,
                    'branch_id' => $mainBranch?->id,
                    'shift_id' => $mainShift?->id,
                    'status' => 'active',
                    'hire_date' => today(),
                    'base_salary' => 0,
                    'employment_type' => 'دوام كامل',
                    'insurance_enabled' => false,
                    'tax_enabled' => false,
                ]);
            } elseif ($employee->status !== 'active') {
                $employee->update(['status' => 'active']);
            }
        }

        if (!$employee || $employee->status !== 'active') {
            if ($strict) {
                if (!$employee) {
                    abort(403, 'لم يتم ربط هذا الحساب بملف موظف. راجع مسؤول الموارد البشرية.');
                }
                abort(403, 'بوابة الخدمة الذاتية متاحة للموظف النشط فقط.');
            }
            return null;
        }

        return $employee;
    }

    private function employee(): Employee
    {
        return $this->resolveEmployee(strict: true);
    }

    public function index()
    {
        $employee = $this->resolveEmployee(strict: false);
        if (!$employee) {
            $user = auth()->user();
            return view('dashboard.employee-portal.inactive', [
                'user' => $user,
                'employee' => $user->employee,
            ]);
        }

        $announcements=InternalAnnouncement::where('status','published')->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>=',now()))
            ->where(fn($q)=>$q->whereNull('branch_id')->orWhere('branch_id',$employee->branch_id))
            ->where(fn($q)=>$q->whereNull('section_id')->orWhere('section_id',$employee->section_id))
            ->withExists(['acknowledgments as acknowledged'=>fn($q)=>$q->where('employee_id',$employee->id)])
            ->latest('published_at')->paginate(20);
        $reviews=PerformanceReview::with('performanceGoals')->where('employee_id',$employee->id)->whereIn('status',['submitted','approved'])->latest('period_end')->get();
        $requests=EmployeeRequest::where('employee_id',$employee->id)->latest()->limit(20)->get();
        $leaves=Leave::with('leaveType')->where('employee_id',$employee->id)->latest('start_date')->limit(20)->get();
        $leaveTypes=LeaveType::where('status','active')->orderBy('name')->get();
        $employees=Employee::where('status','active')->where('id','!=',$employee->id)->orderBy('name')->get(['id','name']);
        return view('dashboard.employee-portal.index',compact('employee','announcements','reviews','requests','leaves','leaveTypes','employees'));
    }

    public function acknowledge(InternalAnnouncement $announcement)
    {
        $employee=$this->employee();abort_unless($announcement->status==='published'&&$announcement->requires_acknowledgment&&(!$announcement->expires_at||$announcement->expires_at->gte(now())),404);
        abort_unless((!$announcement->branch_id||$announcement->branch_id===$employee->branch_id)&&(!$announcement->section_id||$announcement->section_id===$employee->section_id),403);
        $announcement->acknowledgments()->firstOrCreate(['employee_id'=>$employee->id],['acknowledged_at'=>now()]);
        return back()->with('success','تم تسجيل اطلاعك على المحتوى.');
    }

    public function requestLeave(Request $request,LeaveBalanceService $balances)
    {
        $employee=$this->employee();$data=$request->validate(['leave_type_id'=>['required',\Illuminate\Validation\Rule::exists('leave_types','id')->where('status','active')],'start_date'=>'required|date|after_or_equal:today','end_date'=>'required|date|after_or_equal:start_date','reason'=>'nullable|string|max:500','coverage_employee_id'=>['nullable','exists:employees,id',\Illuminate\Validation\Rule::notIn([$employee->id])]]);
        $type=LeaveType::findOrFail($data['leave_type_id']);$start=Carbon::parse($data['start_date']);$end=Carbon::parse($data['end_date']);$days=$balances->countDays($type,$employee,$start,$end);
        if($days<=0)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'الفترة لا تحتوي أيام إجازة محتسبة.']);
        if($type->requires_coverage&&!($data['coverage_employee_id']??null))throw \Illuminate\Validation\ValidationException::withMessages(['coverage_employee_id'=>'حدد الموظف البديل المطلوب.']);
        if($type->max_consecutive_days&&$days>$type->max_consecutive_days)throw \Illuminate\Validation\ValidationException::withMessages(['end_date'=>'تتجاوز الفترة الحد الأقصى المتصل.']);
        if(Leave::where('employee_id',$employee->id)->whereIn('status',['pending','approved'])->whereDate('start_date','<=',$end->toDateString())->whereDate('end_date','>=',$start->toDateString())->exists())throw \Illuminate\Validation\ValidationException::withMessages(['start_date'=>'تتداخل الفترة مع طلب إجازة قائم.']);
        $balances->assertAvailable($employee,$type,$start,$end);Leave::create($data+['employee_id'=>$employee->id,'status'=>'pending','days_count'=>$days]);
        return back()->with('success','تم إرسال طلب الإجازة إلى المدير.');
    }

    public function commentOnReview(Request $request,PerformanceReview $review)
    {
        $employee=$this->employee();abort_unless($review->employee_id===$employee->id,403);abort_unless(in_array($review->status,['submitted','approved'],true),409);
        $data=$request->validate(['employee_comments'=>'required|string|max:3000']);$review->update($data);
        return back()->with('success','تم حفظ تعليقك على مراجعة الأداء.');
    }
}
