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
        $shifts = Shift::all();
        return view('dashboard.shifts.index', compact('shifts'));
    }

    // صفحة إنشاء شيفت جديد
    public function create()
    {
        return view('dashboard.shifts.create');
    }

    // حفظ شيفت جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|unique:shifts,shift_name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'status' => 'required|in:open,closed'
        ]);

        Shift::create($validated);

        return redirect()->route('shifts.index')->with('success', 'تم إضافة الشيفت بنجاح');
    }

    // صفحة تعديل الشيفت
    public function edit(Shift $shift)
    {
        return view('dashboard.shifts.edit', compact('shift'));
    }

    // تحديث بيانات الشيفت
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|unique:shifts,shift_name,' . $shift->id,
            'start_time' => 'required|date_format:H:i:s',  // تعديل هنا
            'end_time' => 'required|date_format:H:i:s',    // تعديل هنا
            'status' => 'required|in:open,closed'
        ]);

        if ($shift->status === 'open' && $validated['status'] === 'closed') {
            // من مفتوح إلى مغلق
            $shift->status = 'closed';
        } elseif ($shift->status === 'closed' && $validated['status'] === 'open') {
            // من مغلق إلى مفتوح
            $shift->status = 'open';
        }

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', 'تم تحديث بيانات الشيفت بنجاح');
    }

    // حذف الشيفت
    public function destroy(Shift $shift)
    {
        if ($shift->status === 'open') {
            return redirect()->route('shifts.index')->with('error', 'لا يمكن حذف شيفت مفتوح');
        }

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'تم حذف الشيفت بنجاح');
    }
}
