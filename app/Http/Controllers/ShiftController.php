<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_shifts')->only(['index', 'show']);
        $this->middleware('permission:store_shifts')->only(['create', 'store']);
        $this->middleware('permission:update_shifts')->only(['edit', 'update']);
        $this->middleware('permission:delete_shifts')->only(['destroy']);
    }

    // عرض كل الشيفتات
    public function index()
    {
        $shifts = Shift::withCount('employees')->orderBy('start_time')->get();
        return view('dashboard.shifts.index', compact('shifts'));
    }

    // صفحة إنشاء شيفت جديد
    public function create()
    {
        abort_unless(auth()->user()->can('store_shifts'), 403);
        return view('dashboard.shifts.create');
    }

    // حفظ شيفت جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|max:255|unique:shifts,shift_name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'status' => 'required|in:open,closed',
            'grace_minutes'=>'required|integer|between:0,240','break_minutes'=>'required|integer|between:0,480','work_days'=>'required|array|min:1','work_days.*'=>'integer|between:0,6','crosses_midnight'=>'required|boolean','overtime_multiplier'=>'required|numeric|between:0.5,5',
        ]);

        Shift::create($validated);

        return redirect()->route('shifts.index')->with('success', 'تم إضافة الشيفت بنجاح');
    }

    // صفحة تعديل الشيفت
    public function edit(Shift $shift)
    {
        abort_unless(auth()->user()->can('update_shifts'), 403);
        return view('dashboard.shifts.edit', compact('shift'));
    }

    // تحديث بيانات الشيفت
    public function update(Request $request, Shift $shift)
    {
        $employeeCount = $shift->employees()->count();
        $validated = $request->validate([
            'shift_name' => 'required|string|max:255|unique:shifts,shift_name,' . $shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'status' => $employeeCount || $shift->assignments()->exists() ? 'required|in:open' : 'required|in:open,closed',
            'grace_minutes'=>'required|integer|between:0,240','break_minutes'=>'required|integer|between:0,480','work_days'=>'required|array|min:1','work_days.*'=>'integer|between:0,6','crosses_midnight'=>'required|boolean','overtime_multiplier'=>'required|numeric|between:0.5,5',
        ]);

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', 'تم تحديث بيانات الشيفت بنجاح');
    }

    // حذف الشيفت
    public function destroy(Shift $shift)
    {
        abort_unless(auth()->user()->can('delete_shifts'), 403);
        if ($shift->status !== 'closed') {
            return redirect()->route('shifts.index')->with('error', 'يرجى إيقاف الوردية قبل حذفها.');
        }

        if ($shift->employees()->exists() || $shift->assignments()->exists()) {
            return redirect()->route('shifts.index')->withErrors(['shift' => 'لا يمكن حذف وردية مرتبطة بموظفين.']);
        }

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'تم حذف الشيفت بنجاح');
    }
}
