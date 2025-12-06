<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bonus;
use App\Models\Employee;
class BonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_bonuses')->only(['index', 'show']);
        $this->middleware('permission:store_bonuses')->only(['create', 'store']);
        $this->middleware('permission:update_bonuses')->only(['edit', 'update']);
        $this->middleware('permission:delete_bonuses')->only(['destroy']);
    }
   

    public function index()
    {
        $bonuses = Bonus::with('employee')->latest()->paginate(10);
        return view('dashboard.bonuses.index', compact('bonuses'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('dashboard.bonuses.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'bonus_date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:applied,approved,rejected',
        ]);

         Bonus::create($validated);
        return redirect()->route('bonuses.index')->with('success', 'Bonus created successfully');
    }

    public function edit(Bonus $bonus)
    {
        $employees = Employee::all();
        return view('dashboard.bonuses.edit', compact('bonus', 'employees'));
    }

    public function update(Request $request, Bonus $bonus)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'bonus_date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:applied,approved,rejected',
        ]);

        $bonus->update($validated);
        return redirect()->route('bonuses.index')->with('success', 'Bonus updated successfully');
    }

    public function destroy(Bonus $bonus)
    {
        if ($bonus->status === 'approved') {
            return redirect()->route('bonuses.index')->with('error', 'Cannot delete an approved bonus');
        }

        $bonus->delete();
        return redirect()->route('bonuses.index')->with('success', 'Bonus deleted successfully');
    }
}
