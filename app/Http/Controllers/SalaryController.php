<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\Bonus;
use App\Models\Deduction;
class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_salaries')->only(['index', 'show']);
        $this->middleware('permission:store_salaries')->only(['store', 'create']);
        $this->middleware('permission:update_salaries')->only(['update', 'edit']);
        $this->middleware('permission:delete_salaries')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Salary::query();

        if ($request->has(['month', 'year'])) {
            $query->whereMonth('payment_date', $request->month)
                ->whereYear('payment_date', $request->year);
        }

        $salaries = $query->with('employee')->latest()->get();

        return view('dashboard.salaries.index', compact('salaries'));
    }

    public function create()
    {
        $employees = Employee::select('id', 'name')->get();
        return view('dashboard.salaries.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payment_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:paid,pending',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $paymentDate = Carbon::parse($validated['payment_date']);

        $exists = Salary::where('employee_id', $employee->id)
            ->whereMonth('payment_date', $paymentDate->month)
            ->whereYear('payment_date', $paymentDate->year)
            ->exists();

        if ($exists) {
            return back()->withErrors(['msg' => 'Salary already exists for this employee in this month']);
        }

        $bonuses = Bonus::where('employee_id', $employee->id)
            ->whereMonth('bonus_date', $paymentDate->month)
            ->whereYear('bonus_date', $paymentDate->year)
            ->sum('amount');

        $deductions = Deduction::where('employee_id', $employee->id)
            ->whereMonth('deduction_date', $paymentDate->month)
            ->whereYear('deduction_date', $paymentDate->year)
            ->sum('amount');

        $baseSalary = $employee->base_salary;
        $total = $baseSalary + $bonuses - $deductions;

        Salary::create([
            'employee_id' => $employee->id,
            'amount' => $baseSalary,
            'total' => $total,
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('salaries.index')->with('success', 'Salary created successfully');
    }

    public function show(Salary $salary)
    {
        $salary->load('employee');
        return view('dashboard.salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        $employees = Employee::all();
        return view('dashboard.salaries.edit', compact('salary', 'employees'));
    }

    public function update(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payment_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:paid,pending',
        ]);

        $paymentDate = Carbon::parse($validated['payment_date']);
        $employeeId = $validated['employee_id'];

        $exists = Salary::where('employee_id', $employeeId)
            ->whereMonth('payment_date', $paymentDate->month)
            ->whereYear('payment_date', $paymentDate->year)
            ->where('id', '!=', $salary->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['msg' => 'Salary already exists for this employee in this month']);
        }

        $employee = Employee::findOrFail($employeeId);
        $baseSalary = $employee->base_salary;

        $bonuses = Bonus::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereMonth('bonus_date', $paymentDate->month)
            ->whereYear('bonus_date', $paymentDate->year)
            ->sum('amount');

        $deductions = Deduction::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereMonth('deduction_date', $paymentDate->month)
            ->whereYear('deduction_date', $paymentDate->year)
            ->sum('amount');

        $total = $baseSalary + $bonuses - $deductions;

        $salary->update([
            'employee_id' => $employeeId,
            'amount' => $baseSalary,
            'total' => $total,
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('salaries.index')->with('success', 'Salary updated successfully');
    }

    public function destroy(Salary $salary)
    {
        if ($salary->status === 'paid') {
            return back()->withErrors(['msg' => 'Cannot delete a paid salary']);
        }

        $salary->delete();

        return redirect()->route('salaries.index')->with('success', 'Salary deleted successfully');
    }
}
