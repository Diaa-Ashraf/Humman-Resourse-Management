<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Shift;
class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_employees')->only(['index', 'show']);
        $this->middleware('permission:store_employees')->only(['create', 'store']);
        $this->middleware('permission:update_employees')->only(['edit', 'update']);
        $this->middleware('permission:delete_employees')->only(['destroy']);
    }

    // صفحة عرض كل الموظفين
    public function index()
    {
        $employees = Employee::with('section', 'shift')->get();
        return view('dashboard.employees.index', compact('employees'));
    }

    // صفحة إنشاء موظف جديد
    public function create()
    {
        $sections = Section::all();
        $shifts = Shift::all();
        return view('dashboard.employees.create', compact('sections', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'section_id' => 'required|exists:sections,id',
            'shift_id' => 'required|exists:shifts,id',
            'status' => 'required|in:active,inactive',
            'hire_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    // صفحة تعديل الموظف
    public function edit(Employee $employee)
    {
        $sections = Section::all();
        $shifts = Shift::all();
        return view('dashboard.employees.edit', compact('employee', 'sections', 'shifts'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'position' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'section_id' => 'required|exists:sections,id',
            'shift_id' => 'required|exists:shifts,id',
            'status' => 'required|in:active,inactive',
            'hire_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
}