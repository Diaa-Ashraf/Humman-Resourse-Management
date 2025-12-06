<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Section;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get current month data
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Employee Statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $newEmployeesThisMonth = Employee::where('hire_date', '>=', $currentMonth)->count();
        
        // Department Statistics
        $totalDepartments = Section::count();
        $departmentsWithEmployees = Section::whereHas('employees')->count();
        
        // Financial Statistics for current month
        $monthlySalaries = Salary::whereBetween('payment_date', [$currentMonth, $endOfMonth])->sum('amount');
        $monthlyBonuses = Bonus::where('status', 'approved')->whereBetween('bonus_date', [$currentMonth, $endOfMonth])->sum('amount');
        $monthlyDeductions = Deduction::where('status', 'approved')->whereBetween('deduction_date', [$currentMonth, $endOfMonth])->sum('amount');
        $netMonthlyPayment = $monthlySalaries + $monthlyBonuses - $monthlyDeductions;
        
        // Attendance Statistics for current month
        $currentMonthAttendances = Attendance::whereBetween('attendance_date', [$currentMonth, $endOfMonth]);
        $totalAttendanceDays = $currentMonthAttendances->count();
        $presentDays = $currentMonthAttendances->where('status', 'present')->count();
        $absentDays = $currentMonthAttendances->where('status', 'absent')->count();
        $lateDays = $currentMonthAttendances->where('status', 'late')->count();
        
        // Leave Statistics for current month
        $currentMonthLeaves = Leave::whereBetween('start_date', [$currentMonth, $endOfMonth])
            ->orWhereBetween('end_date', [$currentMonth, $endOfMonth]);
        $approvedLeaves = $currentMonthLeaves->where('status', 'approved')->get();
        $pendingLeaves = $currentMonthLeaves->where('status', 'pending')->count();
        
        $totalLeaveDays = 0;
        foreach ($approvedLeaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $totalLeaveDays += $startDate->diffInDays($endDate) + 1;
        }
        
        // Recent Activities
        $recentSalaries = Salary::with('employee:id,name')->latest()->take(5)->get();
        $recentBonuses = Bonus::with('employee:id,name')->latest()->take(5)->get();
        $recentAttendances = Attendance::with('employee:id,name')->latest()->take(5)->get();
        
        // Department Performance
        $departmentStats = Section::withCount('employees')
            ->with(['employees' => function($query) use ($currentMonth, $endOfMonth) {
                $query->withSum(['salaries' => function($q) use ($currentMonth, $endOfMonth) {
                    $q->whereBetween('payment_date', [$currentMonth, $endOfMonth]);
                }], 'amount');
            }])
            ->get()
            ->map(function($section) {
                return [
                    'name' => $section->name,
                    'employees' => $section->employees_count,
                    'total_salary' => $section->employees->sum('salaries_sum_amount') ?? 0
                ];
            });
        
        return view('dashboard.index', compact(
            'totalEmployees',
            'activeEmployees',
            'newEmployeesThisMonth',
            'totalDepartments',
            'departmentsWithEmployees',
            'monthlySalaries',
            'monthlyBonuses',
            'monthlyDeductions',
            'netMonthlyPayment',
            'totalAttendanceDays',
            'presentDays',
            'absentDays',
            'lateDays',
            'totalLeaveDays',
            'pendingLeaves',
            'recentSalaries',
            'recentBonuses',
            'recentAttendances',
            'departmentStats'
        ));
    }
}
