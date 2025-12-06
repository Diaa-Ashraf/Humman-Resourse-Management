<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Section;
use Carbon\Carbon;
use App\Models\Employee;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Validate request inputs
        $validated = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'employee_id' => 'nullable|exists:employees,id',
            'section_id' => 'nullable|exists:sections,id',
            'report_type' => 'nullable|in:summary,detailed,attendance,performance'
        ]);

        // التواريخ الافتراضية
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subMonth();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();
        $employeeId = $validated['employee_id'] ?? null;
        $sectionId = $validated['section_id'] ?? null;
        $reportType = $validated['report_type'] ?? 'summary';

        // جلب جميع الموظفين والأقسام لقائمة الاختيار
        $employees = Employee::select('id', 'name', 'section_id')->with('section:id,name')->get();
        $sections = Section::select('id', 'name')->get();

        // بيانات الموظف المحدد
        $employee = $employeeId ? Employee::with(['section', 'shift'])->find($employeeId) : null;

        // Queries للرواتب والبونص والخصومات
        $salaryQuery = Salary::with('employee:id,name,section_id')
            ->whereBetween('payment_date', [$from, $to]);
        $bonusQuery = Bonus::with('employee:id,name,section_id')
            ->where('status', 'approved')->whereBetween('bonus_date', [$from, $to]);
        $deductionQuery = Deduction::with('employee:id,name,section_id')
            ->where('status', 'approved')->whereBetween('deduction_date', [$from, $to]);

        // Queries للحضور والانصراف والإجازات
        $attendanceQuery = Attendance::with('employee:id,name,section_id')
            ->whereBetween('attendance_date', [$from, $to]);
        $leaveQuery = Leave::with(['employee:id,name,section_id', 'leaveType:id,name'])
            ->whereBetween('start_date', [$from, $to])
            ->orWhereBetween('end_date', [$from, $to]);

        // تطبيق الفلاتر
        if ($employeeId) {
            $salaryQuery->where('employee_id', $employeeId);
            $bonusQuery->where('employee_id', $employeeId);
            $deductionQuery->where('employee_id', $employeeId);
            $attendanceQuery->where('employee_id', $employeeId);
            $leaveQuery->where('employee_id', $employeeId);
        }

        if ($sectionId) {
            $employeeIds = Employee::where('section_id', $sectionId)->pluck('id');
            $salaryQuery->whereIn('employee_id', $employeeIds);
            $bonusQuery->whereIn('employee_id', $employeeIds);
            $deductionQuery->whereIn('employee_id', $employeeIds);
            $attendanceQuery->whereIn('employee_id', $employeeIds);
            $leaveQuery->whereIn('employee_id', $employeeIds);
        }

        // الحسابات الإجمالية
        $totalSalaries = $salaryQuery->sum('amount');
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
            $sectionsData = Section::with(['employees' => function($query) use ($from, $to) {
                    $query->whereHas('salaries', function($q) use ($from, $to) {
                        $q->whereBetween('payment_date', [$from, $to]);
                    });
                }])
                ->with(['employees.salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }])
                ->get();

            foreach ($sectionsData as $section) {
                $employeeCount = $section->employees->count();
                $totalSalary = 0;
                
                foreach ($section->employees as $employee) {
                    $totalSalary += $employee->salaries->sum('amount');
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
            $employeesWithPerformance = Employee::select('id', 'name', 'section_id')
                ->with(['section:id,name'])
                ->withCount(['attendances' => function($query) use ($from, $to) {
                    $query->whereBetween('attendance_date', [$from, $to]);
                }])
                ->withSum(['salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }], 'amount')
                ->withSum(['bonuses' => function($query) use ($from, $to) {
                    $query->whereBetween('bonus_date', [$from, $to])->where('status', 'approved');
                }], 'amount')
                ->withSum(['deductions' => function($query) use ($from, $to) {
                    $query->whereBetween('deduction_date', [$from, $to])->where('status', 'approved');
                }], 'amount')
                ->get();

            foreach ($employeesWithPerformance as $emp) {
                $total = ($emp->salaries_sum_amount ?? 0) + ($emp->bonuses_sum_amount ?? 0) - ($emp->deductions_sum_amount ?? 0);
                $employeePerformance[] = [
                    'name' => $emp->name,
                    'section' => $emp->section->name ?? 'N/A',
                    'attendance_days' => $emp->attendances_count,
                    'total_earnings' => $total,
                    'base_salary' => $emp->salaries_sum_amount ?? 0,
                    'bonuses' => $emp->bonuses_sum_amount ?? 0,
                    'deductions' => $emp->deductions_sum_amount ?? 0
                ];
            }
        } else {
            // Show performance for selected employee
            $emp = Employee::with(['section:id,name'])
                ->withCount(['attendances' => function($query) use ($from, $to) {
                    $query->whereBetween('attendance_date', [$from, $to]);
                }])
                ->withSum(['salaries' => function($query) use ($from, $to) {
                    $query->whereBetween('payment_date', [$from, $to]);
                }], 'amount')
                ->withSum(['bonuses' => function($query) use ($from, $to) {
                    $query->whereBetween('bonus_date', [$from, $to])->where('status', 'approved');
                }], 'amount')
                ->withSum(['deductions' => function($query) use ($from, $to) {
                    $query->whereBetween('deduction_date', [$from, $to])->where('status', 'approved');
                }], 'amount')
                ->find($employeeId);

            if ($emp) {
                $total = ($emp->salaries_sum_amount ?? 0) + ($emp->bonuses_sum_amount ?? 0) - ($emp->deductions_sum_amount ?? 0);
                $employeePerformance[] = [
                    'name' => $emp->name,
                    'section' => $emp->section->name ?? 'N/A',
                    'attendance_days' => $emp->attendances_count,
                    'total_earnings' => $total,
                    'base_salary' => $emp->salaries_sum_amount ?? 0,
                    'bonuses' => $emp->bonuses_sum_amount ?? 0,
                    'deductions' => $emp->deductions_sum_amount ?? 0
                ];
            }
        }

        return view('dashboard.reports.index', compact(
            'from',
            'to',
            'employee',
            'employees',
            'sections',
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
            'employeePerformance'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'excel');
        
        // Get the same data as index method
        $validated = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'employee_id' => 'nullable|exists:employees,id',
            'section_id' => 'nullable|exists:sections,id',
            'report_type' => 'nullable|in:summary,detailed,attendance,performance'
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subMonth();
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();
        $employeeId = $validated['employee_id'] ?? null;
        $sectionId = $validated['section_id'] ?? null;
        $reportType = $validated['report_type'] ?? 'summary';

        // Get data (simplified for export)
        $salaryQuery = Salary::with('employee:id,name,section_id')
            ->whereBetween('payment_date', [$from, $to]);
        $bonusQuery = Bonus::with('employee:id,name,section_id')
            ->where('status', 'approved')->whereBetween('bonus_date', [$from, $to]);
        $deductionQuery = Deduction::with('employee:id,name,section_id')
            ->where('status', 'approved')->whereBetween('deduction_date', [$from, $to]);

        if ($employeeId) {
            $salaryQuery->where('employee_id', $employeeId);
            $bonusQuery->where('employee_id', $employeeId);
            $deductionQuery->where('employee_id', $employeeId);
        }

        if ($sectionId) {
            $employeeIds = Employee::where('section_id', $sectionId)->pluck('id');
            $salaryQuery->whereIn('employee_id', $employeeIds);
            $bonusQuery->whereIn('employee_id', $employeeIds);
            $deductionQuery->whereIn('employee_id', $employeeIds);
        }

        $salaries = $salaryQuery->get();
        $bonuses = $bonusQuery->get();
        $deductions = $deductionQuery->get();

        $totalSalaries = $salaries->sum('amount');
        $totalBonuses = $bonuses->sum('amount');
        $totalDeductions = $deductions->sum('amount');
        $netPayment = $totalSalaries + $totalBonuses - $totalDeductions;

        if ($type == 'excel') {
            // Create CSV content
            $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
            $csvContent .= "تقرير الرواتب والمكافآت والخصومات\n";
            $csvContent .= "الفترة: من " . $from->format('Y-m-d') . " إلى " . $to->format('Y-m-d') . "\n\n";
            
            $csvContent .= "ملخص مالي\n";
            $csvContent .= "إجمالي الرواتب," . $totalSalaries . "\n";
            $csvContent .= "إجمالي المكافآت," . $totalBonuses . "\n";
            $csvContent .= "إجمالي الخصومات," . $totalDeductions . "\n";
            $csvContent .= "الصافي," . $netPayment . "\n\n";
            
            $csvContent .= "تفاصيل الرواتب\n";
            $csvContent .= "الموظف,القسم,المبلغ,تاريخ الدفع,ملاحظات\n";
            foreach ($salaries as $salary) {
                $csvContent .= '"' . $salary->employee->name . '","' .
                    ($salary->employee->section->name ?? 'N/A') . '",' .
                    $salary->amount . ',' .
                    $salary->payment_date . ',"' .
                    ($salary->notes ?? '') . '"' . "\n";
            }
            
            $csvContent .= "\nتفاصيل المكافآت\n";
            $csvContent .= "الموظف,القسم,المبلغ,السبب,التاريخ\n";
            foreach ($bonuses as $bonus) {
                $csvContent .= '"' . $bonus->employee->name . '","' .
                    ($bonus->employee->section->name ?? 'N/A') . '",' .
                    $bonus->amount . ',"' .
                    $bonus->reason . '",' .
                    $bonus->bonus_date . '"' . "\n";
            }
            
            $csvContent .= "\nتفاصيل الخصومات\n";
            $csvContent .= "الموظف,القسم,المبلغ,السبب,التاريخ\n";
            foreach ($deductions as $deduction) {
                $csvContent .= '"' . $deduction->employee->name . '","' .
                    ($deduction->employee->section->name ?? 'N/A') . '",' .
                    $deduction->amount . ',"' .
                    $deduction->reason . '",' .
                    $deduction->deduction_date . '"' . "\n";
            }

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
                        <td>' . ($salary->employee->section->name ?? 'N/A') . '</td>
                        <td>' . number_format($salary->amount, 2) . '</td>
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

