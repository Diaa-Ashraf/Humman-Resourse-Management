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
        $types = LeaveType::withCount('leaves')->orderBy('name')->paginate(20);
        return view('dashboard.LeaveTypes.index', compact('types'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('store_leave_types'), 403);
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
            'annual_entitlement_days'=>'nullable|numeric|min:0|max:365','carryover_cap_days'=>'nullable|numeric|min:0|max:365','max_consecutive_days'=>'nullable|integer|min:1|max:365','exclude_weekends'=>'required|boolean','requires_coverage'=>'required|boolean',
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
        abort_unless(auth()->user()->can('update_leave_types'), 403);
        return view('dashboard.LeaveTypes.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $leaveType->id,
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'max_days' => 'nullable|integer|min:1',
            'annual_entitlement_days'=>'nullable|numeric|min:0|max:365','carryover_cap_days'=>'nullable|numeric|min:0|max:365','max_consecutive_days'=>'nullable|integer|min:1|max:365','exclude_weekends'=>'required|boolean','requires_coverage'=>'required|boolean',
        ]);

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')
            ->with('success', 'تم تعديل نوع الإجازة بنجاح');
    }

    public function destroy(LeaveType $leaveType)
    {
        abort_unless(auth()->user()->can('delete_leave_types'), 403);
        if ($leaveType->status === 'active' || $leaveType->leaves()->exists()) {
            return back()->withErrors(['msg' => 'لا يمكن حذف نوع إجازة نشط أو مرتبط بطلبات إجازة.']);
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')
            ->with('success', 'تم حذف نوع الإجازة بنجاح');
    }
}
