<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_leave_types')->only(['index', 'show']);
        $this->middleware('permission:store_leave_types')->only(['create', 'store']);
        $this->middleware('permission:update_leave_types')->only(['edit', 'update']);
        $this->middleware('permission:delete_leave_types')->only(['destroy']);
    }

    public function index()
    {
        $types = LeaveType::all();
        return view('dashboard.LeaveTypes.index', compact('types'));
    }

    public function create()
    {
        return view('dashboard.LeaveTypes.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'max_days' => 'nullable|integer|min:1',
        ]);

        LeaveType::create($validated);

        return redirect()->route('leave-types.index')
            ->with('success', 'تم إنشاء نوع الإجازة بنجاح');
    }

    public function show(LeaveType $leaveType)
    {
        return view('dashboard.LeaveTypes.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType)
    {
        return view('dashboard.LeaveTypes.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveType->id,
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'max_days' => 'nullable|integer|min:1',
        ]);

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')
            ->with('success', 'تم تعديل نوع الإجازة بنجاح');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->status === 'active') {
            return back()->withErrors(['msg' => 'لا يمكن حذف نوع إجازة نشط']);
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')
            ->with('success', 'تم حذف نوع الإجازة بنجاح');
    }
}
