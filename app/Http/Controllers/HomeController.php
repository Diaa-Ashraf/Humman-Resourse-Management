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
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use App\Services\BranchAccess;

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
        $access=app(BranchAccess::class);$user=auth()->user();$scope=$access->branchIds($user);
        // Get current month data
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Employee Statistics
        $canViewEmployees = auth()->user()->can('show_employees');
        $employeesQuery=$access->employees(Employee::query(),$user);
        $totalEmployees = $canViewEmployees ? (clone $employeesQuery)->count() : 0;
        $activeEmployees = $canViewEmployees ? (clone $employeesQuery)->where('status', 'active')->count() : 0;
        $newEmployeesThisMonth = $canViewEmployees ? (clone $employeesQuery)->whereBetween('hire_date', [$currentMonth, $endOfMonth])->count() : 0;
        $branchQuery = $user->can('show_branches') ? Branch::when(!$access->isGlobal($user),fn($q)=>$q->whereIn('id',$scope)) : Branch::whereRaw('1 = 0');
        
        // Department Statistics
        $sectionQuery=Section::when(!$access->isGlobal($user),fn($q)=>$q->whereIn('branch_id',$scope));
        $totalDepartments = $user->can('show_sections') ? (clone $sectionQuery)->count() : 0;
        $departmentsWithEmployees = $user->can('show_sections') ? (clone $sectionQuery)->whereHas('employees',fn($q)=>$access->employees($q,$user))->count() : 0;
        
        // Financial Statistics for current month
        $monthlySalaries = $user->can('show_salaries') ? $access->throughEmployee(Salary::query(),$user)->whereBetween('payment_date', [$currentMonth, $endOfMonth])->sum('total') : 0;
        $monthlyBonuses = 0;
        $monthlyDeductions = 0;
        $netMonthlyPayment = $monthlySalaries;
        
        // Attendance Statistics for current month
        $currentMonthAttendances = $user->can('show_attendances') ? $access->throughEmployee(Attendance::query(),$user)->whereBetween('attendance_date', [$currentMonth, $endOfMonth]) : Attendance::whereRaw('1 = 0');
        $totalAttendanceDays = (clone $currentMonthAttendances)->count();
        $presentDays = (clone $currentMonthAttendances)->where('status', 'present')->count();
        $absentDays = (clone $currentMonthAttendances)->where('status', 'absent')->count();
        $lateDays = (clone $currentMonthAttendances)->where('status', 'late')->count();
        
        // Leave Statistics for current month
        $currentMonthLeaves = Leave::where(fn($query) => $query
            ->whereBetween('start_date', [$currentMonth, $endOfMonth])
            ->orWhereBetween('end_date', [$currentMonth, $endOfMonth]));
        $access->throughEmployee($currentMonthLeaves,$user);
        if (!auth()->user()->can('show_leaves')) {
            $currentMonthLeaves->whereRaw('1 = 0');
        }
        $approvedLeaves = auth()->user()->can('show_leaves') ? (clone $currentMonthLeaves)->where('status', 'approved')->get() : collect();
        $pendingLeaves = auth()->user()->can('show_leaves') ? (clone $currentMonthLeaves)->where('status', 'pending')->count() : 0;
        $pendingBonuses = $user->can('review_bonuses') ? $access->throughEmployee(Bonus::query(),$user)->where('status', 'applied')->count() : 0;
        $pendingDeductions = $user->can('review_deductions') ? $access->throughEmployee(Deduction::query(),$user)->where('status', 'applied')->count() : 0;
        $pendingLeavesForReview = $user->can('review_leaves') ? $access->throughEmployee(Leave::query(),$user)->where('status', 'pending')->count() : 0;
        if (auth()->user()->can('review_leaves')) {
            $pendingLeaves = $pendingLeavesForReview;
        }
        
        $totalLeaveDays = 0;
        foreach ($approvedLeaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $totalLeaveDays += $startDate->diffInDays($endDate) + 1;
        }
        
        // Recent Activities
        $recentSalaries = $user->can('show_salaries') ? $access->throughEmployee(Salary::query(),$user)->with('employee:id,name')->latest('payment_date')->take(5)->get() : collect();
        $recentBonuses = $user->can('show_bonuses') ? $access->throughEmployee(Bonus::query(),$user)->with('employee:id,name')->latest('bonus_date')->take(5)->get() : collect();
        $recentAttendances = $user->can('show_attendances') ? $access->throughEmployee(Attendance::query(),$user)->with('employee:id,name')->latest('attendance_date')->take(5)->get() : collect();
        
        // Department Performance
        $canViewSalaries = auth()->user()->can('show_salaries');
        $departmentStats = ($user->can('show_employees') || $user->can('show_sections')) ? (clone $sectionQuery)->when($canViewEmployees, fn($query) => $query->withCount(['employees'=>fn($q)=>$access->employees($q,$user)]))
            ->when($canViewEmployees && $canViewSalaries, fn($query) => $query->with(['employees' => function($query) use ($currentMonth, $endOfMonth, $access, $user) {
                $access->employees($query,$user);
                $query->withSum(['salaries' => function($q) use ($currentMonth, $endOfMonth) {
                    $q->whereBetween('payment_date', [$currentMonth, $endOfMonth]);
                }], 'amount');
            }]))
            ->get()
            ->take(8)
            ->map(function($section) {
                return [
                    'name' => $section->name,
                    'employees' => $section->employees_count ?? 0,
                    'total_salary' => $section->employees?->sum('salaries_sum_amount') ?? 0
                ];
            }) : collect();
        
        $totalBranches = (clone $branchQuery)->count();
        $activeBranches = (clone $branchQuery)->where('is_active', true)->count();
        $totalUsers = $user->can('show_users') || $user->can('assign_roles') ? User::count() : 0;
        $userLimit = 10;
        $branchCapacity = 20;
        $employeeCapacity = 1000;
        $todayAttendance = $user->can('show_attendances') ? $access->throughEmployee(Attendance::query(),$user)->whereDate('attendance_date', Carbon::today())->whereIn('status', ['present', 'late'])->count() : 0;
        $todayPresent = $user->can('show_attendances') ? $access->throughEmployee(Attendance::query(),$user)->whereDate('attendance_date', Carbon::today())->where('status', 'present')->count() : 0;
        $todayAttendanceRate = ($activeEmployees && auth()->user()->can('show_attendances')) ? min(100, round($todayPresent / $activeEmployees * 100)) : 0;
        $recentHires = $user->can('show_employees')
            ? $access->employees(Employee::query(),$user)->with(['section:id,name', 'branch:id,name'])->latest('hire_date')->limit(5)->get()
            : collect();

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
            'departmentStats', 'totalBranches', 'activeBranches', 'totalUsers', 'userLimit',
            'branchCapacity', 'employeeCapacity', 'todayAttendance', 'todayPresent', 'todayAttendanceRate', 'recentHires',
            'pendingBonuses', 'pendingDeductions', 'pendingLeavesForReview'
        ));
    }
}
