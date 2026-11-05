<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Section;
use App\Models\Branch;
use Carbon\Carbon;
use App\Models\Employee;
use App\Services\BranchAccess;
use App\Models\{EmployeeDocument, EmployeeRequest, JobOpening, TrainingCourse, TrainingEnrollment};
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:show_reports')->only(['index', 'export']);
    }
    public function index(Request $request)
    {
        // Validate request inputs
        $validated = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'employee_id' => 'nullable|exists:employees,id',
            'section_id' => 'nullable|exists:sections,id',
            'branch_id' => 'nullable|exists:branches,id',
            'report_type' => 'nullable|in:summary,detailed,attendance,performance'
        ]);

        // التواريخ الافتراضية
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subMonth();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();
        abort_if($to->lt($from), 422, 'يجب أن يكون تاريخ النهاية بعد تاريخ البداية.');
        $employeeId = $validated['employee_id'] ?? null;
        $sectionId = $validated['section_id'] ?? null;
        $reportType = $validated['report_type'] ?? 'summary';
        $branchId = $validated['branch_id'] ?? null;

        // جلب جميع الموظفين والأقسام لقائمة الاختيار
        $access=app(BranchAccess::class);if($branchId)$access->assertBranch((int)$branchId,auth()->user());
        $employees = $access->employees(Employee::query(),auth()->user())->select('id', 'name', 'section_id', 'branch_id')->with(['section:id,name', 'branch:id,name'])->orderBy('name')->get()
            ->filter(fn($candidate) => (!$employeeId || $candidate->id == $employeeId) && (!$sectionId || $candidate->section_id == $sectionId) && (!$branchId || $candidate->branch_id == $branchId))
            ->values();
        $sections = Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->select('id', 'name')->orderBy('name')->get();
        $branches = Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->select('id', 'name')->orderBy('name')->get();

        // بيانات الموظف المحدد
        $employee = $employeeId
            ? $access->employees(Employee::query(),auth()->user())->with(['section', 'shift'])->whereKey($employeeId)->when($sectionId, fn($query) => $query->where('section_id', $sectionId))->when($branchId, fn($query) => $query->where('branch_id', $branchId))->first()
            : null;

        // Queries للرواتب والبونص والخصومات
        $salaryQuery = Salary::with('employee:id,name,section_id')->with('employee.section:id,name')
            ->whereBetween('payment_date', [$from, $to]);
        $bonusQuery = Bonus::with('employee:id,name,section_id')->with('employee.section:id,name')
            ->where('status', 'approved')->whereBetween('bonus_date', [$from, $to]);
        $deductionQuery = Deduction::with('employee:id,name,section_id')->with('employee.section:id,name')
            ->where('status', 'approved')->whereBetween('deduction_date', [$from, $to]);

        // Queries للحضور والانصراف والإجازات
        $attendanceQuery = Attendance::with('employee:id,name,section_id')->with('employee.section:id,name')
            ->whereBetween('attendance_date', [$from, $to]);
        $leaveQuery = Leave::with(['employee:id,name,section_id', 'employee.section:id,name', 'leaveType:id,name'])
            ->where(fn($query) => $query->whereBetween('start_date', [$from, $to])
                ->orWhereBetween('end_date', [$from, $to]));

        // تطبيق الفلاتر
        $employeeIds = $access->employees(Employee::query(),auth()->user())
            ->when($employeeId, fn($query) => $query->whereKey($employeeId))
            ->when($sectionId, fn($query) => $query->where('section_id', $sectionId))
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->pluck('id');
        foreach ([$salaryQuery, $bonusQuery, $deductionQuery, $attendanceQuery, $leaveQuery] as $query) {
            $query->whereIn('employee_id', $employeeIds);
        }
        $employees = $employees->filter(fn($candidate) =>
            (!$employeeId || $candidate->id == $employeeId)
            && (!$sectionId || $candidate->section_id == $sectionId)
            && (!$branchId || $candidate->branch_id == $branchId)
        )->values();

        // الحسابات الإجمالية
        $totalSalaries = $salaryQuery->sum('total');
        $totalBonuses = $bonusQuery->sum('amount');
        $totalDeductions = $deductionQuery->sum('amount');

        $salaryCount = $salaryQuery->count();
        $bonusCount = $bonusQuery->count();
        $deductionCount = $deductionQuery->count();

        $netPayment = $totalSalaries + $totalBonuses - $totalDeductions;

        // بيانات مفصلة
        $salaries = $salaryQuery->get();
        $bonuses = $bonusQuery->get();
        $deductions = $deductionQuery->get();
        $attendances = $attendanceQuery->get();
        $leaves = $leaveQuery->get();

        // إحصائيات الحضور
        $totalAttendanceDays = $attendances->count();
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $lateDays = $attendances->where('status', 'late')->count();

        // إحصائيات الإجازات
        $totalLeaveDays = 0;
        $approvedLeaves = $leaves->where('status', 'approved');
        foreach ($approvedLeaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $totalLeaveDays += $startDate->diffInDays($endDate) + 1;
        }

        // متوسطات الرواتب حسب القسم
        $sectionStats = [];
        if (!$employeeId) {
            // Get sections with employee count and salary totals
            $sectionsData = Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->with(['employees' => function($query) use ($from, $to, $access) {
                    $access->employees($query,auth()->user());$query->whereHas('salaries', function($q) use ($from, $to) {
                        $q->whereBetween('payment_date', [$from, $to]);
                    });
                }])
                ->with(['employees.salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }])
            ->when($sectionId, fn($query) => $query->whereKey($sectionId))
            ->when(!empty($validated['branch_id']), fn($query) => $query->whereHas('employees', fn($employeeQuery) => $employeeQuery->where('branch_id', $validated['branch_id'])))
            ->get();

            foreach ($sectionsData as $section) {
                $employeeCount = $section->employees->count();
                $totalSalary = 0;
                
                foreach ($section->employees as $employee) {
                    $totalSalary += $employee->salaries->sum('total');
                }
                
                if ($employeeCount > 0) {
                    $sectionStats[] = [
                        'name' => $section->name,
                        'employees' => $employeeCount,
                        'total_salary' => $totalSalary,
                        'avg_salary' => $totalSalary / $employeeCount
                    ];
                }
            }
        }

        // أداء الموظفين
        $employeePerformance = [];
        if (!$employeeId) {
            $employeesWithPerformance = $access->employees(Employee::query(),auth()->user())->select('id', 'name', 'section_id')
                ->when($sectionId, fn($query) => $query->where('section_id', $sectionId))
                ->when(!empty($validated['branch_id']), fn($query) => $query->where('branch_id', $validated['branch_id']))
                ->with(['section:id,name'])
                ->withCount(['attendances' => function($query) use ($from, $to) {
                    $query->whereBetween('attendance_date', [$from, $to]);
                }])
                ->withSum(['salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }], 'total')
                ->get();

            foreach ($employeesWithPerformance as $emp) {
                $total = ($emp->salaries_sum_total ?? 0);
                $employeePerformance[] = [
                    'name' => $emp->name,
                    'section' => $emp->section->name ?? 'غير محدد',
                    'attendance_days' => $emp->attendances_count,
                    'total_earnings' => $total,
                    'base_salary' => 0,
                    'bonuses' => 0,
                    'deductions' => 0
                ];
            }
        } else {
            // Show performance for selected employee
            $emp = $access->employees(Employee::query(),auth()->user())->with(['section:id,name'])
                ->when($sectionId, fn($query) => $query->where('section_id', $sectionId))
                ->when(!empty($validated['branch_id']), fn($query) => $query->where('branch_id', $validated['branch_id']))
                ->withCount(['attendances' => function($query) use ($from, $to) {
                    $query->whereBetween('attendance_date', [$from, $to]);
                }])
                ->withSum(['salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }], 'total')
                ->find($employeeId);

            if ($emp) {
                $total = ($emp->salaries_sum_total ?? 0);
                $employeePerformance[] = [
                    'name' => $emp->name,
                    'section' => $emp->section->name ?? 'غير محدد',
                    'attendance_days' => $emp->attendances_count,
                    'total_earnings' => $total,
                    'base_salary' => 0,
                    'bonuses' => 0,
                    'deductions' => 0
                ];
            }
        }

        $scopedEmployees=$access->employees(Employee::query(),auth()->user())->when($employeeId,fn($q)=>$q->whereKey($employeeId));
        $headcountAtStart=(clone $scopedEmployees)->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->when($branchId,fn($q)=>$q->where('branch_id',$branchId))->whereDate('hire_date','<=',$from)->where(fn($q)=>$q->whereNull('termination_date')->orWhereDate('termination_date','>=',$from))->count();
        $headcountAtEnd=(clone $scopedEmployees)->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->when($branchId,fn($q)=>$q->where('branch_id',$branchId))->whereDate('hire_date','<=',$to)->where(fn($q)=>$q->whereNull('termination_date')->orWhereDate('termination_date','>',$to))->count();
        $terminations=$access->employees(Employee::query(),auth()->user())->whereBetween('termination_date',[$from->toDateString(),$to->toDateString()])->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->when($branchId,fn($q)=>$q->where('branch_id',$branchId))->count();
        $turnoverRate=(($headcountAtStart+$headcountAtEnd)>0)?round($terminations/(($headcountAtStart+$headcountAtEnd)/2)*100,1):0;
        $openVacancies=JobOpening::where('status','open')->where('approval_status','approved')->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->when($branchId,fn($q)=>$q->where('branch_id',$branchId))->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->sum('headcount');
        $trainingCourses=TrainingCourse::whereBetween('starts_at',[$from->toDateString(),$to->toDateString()])->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->when($branchId,fn($q)=>$q->where('branch_id',$branchId));
        $trainingCost=(clone $trainingCourses)->sum('cost');$trainingCourseCount=(clone $trainingCourses)->count();
        $trainingHours=TrainingEnrollment::whereHas('employee',fn($q)=>$access->employees($q,auth()->user())->when($sectionId,fn($employeeQuery)=>$employeeQuery->where('section_id',$sectionId))->when($branchId,fn($employeeQuery)=>$employeeQuery->where('branch_id',$branchId)))->whereHas('course',fn($q)=>$q->whereBetween('starts_at',[$from->toDateString(),$to->toDateString()]))->sum('attended_hours');
        $documentAlerts=$access->throughEmployee(EmployeeDocument::query(),auth()->user())->whereHas('employee',fn($q)=>$q->when($sectionId,fn($e)=>$e->where('section_id',$sectionId))->when($branchId,fn($e)=>$e->where('branch_id',$branchId)))->whereNotNull('expires_at')->whereDate('expires_at','<=',today()->addDays(30))->count();
        $overdueRequests=$access->throughEmployee(EmployeeRequest::query(),auth()->user())->whereHas('employee',fn($q)=>$q->when($sectionId,fn($e)=>$e->where('section_id',$sectionId))->when($branchId,fn($e)=>$e->where('branch_id',$branchId)))->where('status','pending')->where('created_at','<',now()->subDays(5))->count();
        $comparisonBranches=Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->when($branchId,fn($q)=>$q->whereKey($branchId))->orderBy('name')->get();
        $comparisonIds=$comparisonBranches->modelKeys();
        $branchHeads=DB::table('employees')->select('branch_id',DB::raw('COUNT(*) as total'))->whereIn('branch_id',$comparisonIds)->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->when($employeeId,fn($q)=>$q->where('id',$employeeId))->where('status','active')->groupBy('branch_id')->pluck('total','branch_id');
        $branchPayroll=DB::table('salaries')->join('employees','employees.id','=','salaries.employee_id')->select('employees.branch_id',DB::raw('SUM(salaries.total) as total'))->whereIn('employees.branch_id',$comparisonIds)->when($sectionId,fn($q)=>$q->where('employees.section_id',$sectionId))->when($employeeId,fn($q)=>$q->where('employees.id',$employeeId))->whereBetween('salaries.payment_date',[$from->toDateString(),$to->toDateString()])->groupBy('employees.branch_id')->pluck('total','branch_id');
        $branchAttendance=DB::table('attendances')->join('employees','employees.id','=','attendances.employee_id')->select('employees.branch_id',DB::raw('COUNT(*) as total'),DB::raw("SUM(CASE WHEN attendances.status = 'absent' THEN 1 ELSE 0 END) as absent"),DB::raw("SUM(CASE WHEN attendances.status = 'late' THEN 1 ELSE 0 END) as late"))->whereIn('employees.branch_id',$comparisonIds)->when($sectionId,fn($q)=>$q->where('employees.section_id',$sectionId))->when($employeeId,fn($q)=>$q->where('employees.id',$employeeId))->whereBetween('attendances.attendance_date',[$from->toDateString(),$to->toDateString()])->groupBy('employees.branch_id')->get()->keyBy('branch_id');
        $branchComparison=$comparisonBranches->map(function($branch)use($branchHeads,$branchPayroll,$branchAttendance){$attendance=$branchAttendance->get($branch->id);return ['name'=>$branch->name,'employees'=>(int)($branchHeads[$branch->id]??0),'payroll'=>(float)($branchPayroll[$branch->id]??0),'attendance'=>(int)($attendance->total??0),'absence'=>(int)($attendance->absent??0),'late'=>(int)($attendance->late??0)];});

        return view('dashboard.reports.index', compact(
            'from',
            'to',
            'employee',
            'employees',
            'sections',
            'branches',
            'reportType',
            'totalSalaries',
            'totalBonuses',
            'totalDeductions',
            'salaryCount',
            'bonusCount',
            'deductionCount',
            'netPayment',
            'salaries',
            'bonuses',
            'deductions',
            'attendances',
            'leaves',
            'totalAttendanceDays',
            'presentDays',
            'absentDays',
            'lateDays',
            'totalLeaveDays',
            'sectionStats',
            'employeePerformance',
            'headcountAtEnd','terminations','turnoverRate','openVacancies','trainingCost','trainingCourseCount','trainingHours','documentAlerts','overdueRequests','branchComparison'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->validate(['type'=>'nullable|in:excel,pdf'])['type'] ?? 'excel';
        
        // Get the same data as index method
        $validated = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'employee_id' => 'nullable|exists:employees,id',
            'section_id' => 'nullable|exists:sections,id',
            'branch_id' => 'nullable|exists:branches,id',
            'report_type' => 'nullable|in:summary,detailed,attendance,performance'
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subMonth();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();
        abort_if($to->lt($from), 422, 'يجب أن يكون تاريخ النهاية بعد تاريخ البداية.');
        $employeeId = $validated['employee_id'] ?? null;
        $sectionId = $validated['section_id'] ?? null;
        $branchId = $validated['branch_id'] ?? null;
        $reportType = $validated['report_type'] ?? 'summary';

        $access=app(BranchAccess::class);if($branchId)$access->assertBranch((int)$branchId,auth()->user());
        $employees = $access->employees(Employee::query(),auth()->user())->select('id', 'name', 'section_id', 'branch_id')->with(['section:id,name', 'branch:id,name'])->orderBy('name')->get()
            ->filter(fn($employee) => (!$employeeId || $employee->id == $employeeId) && (!$sectionId || $employee->section_id == $sectionId) && (!$branchId || $employee->branch_id == $branchId));
        $sections = Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->orderBy('name')->get();
        $branches = Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->orderBy('name')->get();

        // Get data (simplified for export)
        $salaryQuery = Salary::with(['employee:id,name,section_id', 'employee.section:id,name'])
            ->whereBetween('payment_date', [$from, $to]);
        $bonusQuery = Bonus::with(['employee:id,name,section_id', 'employee.section:id,name'])
            ->where('status', 'approved')->whereBetween('bonus_date', [$from, $to]);
        $deductionQuery = Deduction::with(['employee:id,name,section_id', 'employee.section:id,name'])
            ->where('status', 'approved')->whereBetween('deduction_date', [$from, $to]);

        $scopedEmployeeIds=$access->employees(Employee::query(),auth()->user())->when($employeeId,fn($q)=>$q->whereKey($employeeId))->when($sectionId,fn($q)=>$q->where('section_id',$sectionId))->when($branchId,fn($q)=>$q->where('branch_id',$branchId))->pluck('id');
        $salaryQuery->whereIn('employee_id',$scopedEmployeeIds);$bonusQuery->whereIn('employee_id',$scopedEmployeeIds);$deductionQuery->whereIn('employee_id',$scopedEmployeeIds);

        if ($sectionId) {
            $employeeIds = $access->employees(Employee::query(),auth()->user())->where('section_id', $sectionId)->pluck('id');
            $salaryQuery->whereIn('employee_id', $employeeIds);
            $bonusQuery->whereIn('employee_id', $employeeIds);
            $deductionQuery->whereIn('employee_id', $employeeIds);
        }

        if ($branchId) {
            $employeeIds = $access->employees(Employee::query(),auth()->user())->where('branch_id', $branchId)->pluck('id');
            $salaryQuery->whereIn('employee_id', $employeeIds);
            $bonusQuery->whereIn('employee_id', $employeeIds);
            $deductionQuery->whereIn('employee_id', $employeeIds);
        }

        $salaries = $salaryQuery->get();
        $bonuses = $bonusQuery->get();
        $deductions = $deductionQuery->get();

        $totalSalaries = $salaries->sum('total');
        $totalBonuses = $bonusQuery->sum('amount');
        $totalDeductions = $deductionQuery->sum('amount');
        $netPayment = $totalSalaries + $totalBonuses - $totalDeductions;

        if ($type == 'excel') {
            $stream = fopen('php://temp', 'w+b');
            fwrite($stream, "\xEF\xBB\xBF");
            $writeRow = static fn (array $row) => fputcsv($stream, $row, ',', '"', '\\', "\r\n");
            $writeRow(['تقرير الرواتب والمكافآت والخصومات']);
            $writeRow(['الفترة', 'من '.$from->format('Y-m-d').' إلى '.$to->format('Y-m-d')]);
            $writeRow([]);
            $writeRow(['ملخص مالي']);
            $writeRow(['إجمالي الرواتب', $totalSalaries]);
            $writeRow(['إجمالي المكافآت', $totalBonuses]);
            $writeRow(['إجمالي الخصومات', $totalDeductions]);
            $writeRow(['الصافي', $netPayment]);
            $writeRow([]);
            $writeRow(['تفاصيل الرواتب']);
            $writeRow(['الموظف', 'القسم', 'المبلغ', 'تاريخ الدفع', 'ملاحظات']);
            foreach ($salaries as $salary) $writeRow([$salary->employee->name ?? '', $salary->employee->section->name ?? 'غير محدد', $salary->total, $salary->payment_date, $salary->notes ?? '']);
            $writeRow([]);
            $writeRow(['تفاصيل المكافآت']);
            $writeRow(['الموظف', 'القسم', 'المبلغ', 'السبب', 'التاريخ']);
            foreach ($bonuses as $bonus) $writeRow([$bonus->employee->name ?? '', $bonus->employee->section->name ?? 'غير محدد', $bonus->amount, $bonus->reason, $bonus->bonus_date]);
            $writeRow([]);
            $writeRow(['تفاصيل الخصومات']);
            $writeRow(['الموظف', 'القسم', 'المبلغ', 'السبب', 'التاريخ']);
            foreach ($deductions as $deduction) $writeRow([$deduction->employee->name ?? '', $deduction->employee->section->name ?? 'غير محدد', $deduction->amount, $deduction->reason, $deduction->deduction_date]);
            rewind($stream);
            $csvContent = stream_get_contents($stream);
            fclose($stream);

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.csv"');
        }
        
        if ($type == 'pdf') {
            // For PDF, we'll create a simple HTML that can be converted
            $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير الموارد البشرية</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .summary { background-color: #f9f9f9; padding: 15px; margin: 20px 0; }
        .header { text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">تقرير الرواتب والمكافآت والخصومات</div>
    <div class="summary">
        <p><strong>الفترة:</strong> من ' . $from->format('Y-m-d') . ' إلى ' . $to->format('Y-m-d') . '</p>
        <p><strong>إجمالي الرواتب:</strong> ' . number_format($totalSalaries, 2) . ' جنيه</p>
        <p><strong>إجمالي المكافآت:</strong> ' . number_format($totalBonuses, 2) . ' جنيه</p>
        <p><strong>إجمالي الخصومات:</strong> ' . number_format($totalDeductions, 2) . ' جنيه</p>
        <p><strong>الصافي:</strong> ' . number_format($netPayment, 2) . ' جنيه</p>
    </div>';
            
            if ($salaries->count() > 0) {
                $html .= '<h3>تفاصيل الرواتب</h3>
                <table>
                    <tr>
                        <th>الموظف</th>
                        <th>القسم</th>
                        <th>المبلغ</th>
                        <th>تاريخ الدفع</th>
                        <th>ملاحظات</th>
                    </tr>';
                foreach ($salaries as $salary) {
                    $html .= '<tr>
                        <td>' . $salary->employee->name . '</td>
                        <td>' . ($salary->employee->section->name ?? 'غير محدد') . '</td>
                        <td>' . number_format($salary->total, 2) . '</td>
                        <td>' . $salary->payment_date . '</td>
                        <td>' . ($salary->notes ?? '-') . '</td>
                    </tr>';
                }
                $html .= '</table>';
            }
            
            $html .= '</body></html>';
            
            return response($html)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.html"');
        }
        
        return response()->json(['message' => 'Invalid export type']);
    }
}

