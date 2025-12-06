<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Http\Request;
class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_leaves')->only(['index', 'show']);
        $this->middleware('permission:store_leaves')->only(['create', 'store']);
        $this->middleware('permission:update_leaves')->only(['edit', 'update']);
        $this->middleware('permission:delete_leaves')->only(['destroy']);
    }

    public function index()
    {
        $leaves = Leave::with('employee', 'leaveType')->latest()->get();
        return view('dashboard.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $employees = Employee::all();
        $types = LeaveType::all();

        return view('dashboard.leaves.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|in:approved,pending,rejected',
        ]);

        Leave::create($validated);

        return redirect()->route('leaves.index')->with('success', 'تم إنشاء طلب الإجازة بنجاح');
    }

    public function show(Leave $leave)
    {
        $leave->load('employee', 'leaveType');
        return view('dashboard.leaves.show', compact('leave'));
    }

    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        $employees = Employee::select('id', 'name')->get();
        $types = LeaveType::select('id', 'name')->get();

        return view('dashboard.leaves.edit', compact('leave', 'employees', 'types'));
    }


    public function update(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|in:approved,pending,rejected',
        ]);

        $leave->update($validated);

        return redirect()->route('leaves.index')->with('success', 'تم تعديل طلب الإجازة بنجاح');
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status === 'approved') {
            return back()->withErrors(['msg' => 'لا يمكن حذف طلب إجازة معتمد']);
        }

        $leave = $leave->delete();
        // dd($leave);

        return redirect()->route('leaves.index')->with('success', 'تم حذف طلب الإجازة بنجاح');
    }
}
