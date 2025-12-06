<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deduction;
use App\Models\Employee;
class DeductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_deductions')->only(['index', 'show']);
        $this->middleware('permission:store_deductions')->only(['create', 'store']);
        $this->middleware('permission:update_deductions')->only(['edit', 'update']);
        $this->middleware('permission:delete_deductions')->only(['destroy']);
    }
   

    public function index()
    {
        $deductions = Deduction::with('employee')->latest()->paginate(10);
        return view('dashboard.deductions.index', compact('deductions'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('dashboard.deductions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'deduction_date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:applied,approved,rejected',
        ]);

        Deduction::create($validated);

        return redirect()->route('deductions.index')->with('success', 'Deduction created successfully');
    }

    public function edit(Deduction $deduction)
    {
        $employees = Employee::all();
        return view('dashboard.deductions.edit', compact('deduction', 'employees'));
    }

    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'deduction_date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:applied,approved,rejected',
        ]);

        $deduction->update($validated);

        return redirect()->route('deductions.index')->with('success', 'Deduction updated successfully');
    }

    public function destroy(Deduction $deduction)
    {
        if ($deduction->status === 'approved') {
            return redirect()->route('deductions.index')->with('error', 'Cannot delete an active deduction');
        }
        $deduction->delete();

        return redirect()->route('deductions.index')->with('success', 'Deduction deleted successfully');
    }
}
