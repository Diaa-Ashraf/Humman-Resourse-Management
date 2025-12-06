<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Section;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // عرض صفحة الحضور
    public function index(Request $request)
    {
        $query = Attendance::with('employee.section')->latest('attendance_date');

        // الفلاتر
        if ($request->date) {
            $query->whereDate('attendance_date', $request->date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->section) {
            $query->whereHas('employee', fn($q) => $q->where('section_id', $request->section));
        }

        $attendances = $query->paginate(15);
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $sections = Section::orderBy('name')->get();

        return view('dashboard.attendance.index', compact('attendances', 'employees', 'sections'));
    }

    // تسجيل حضور
    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_in' => 'required'
        ]);

        $today = Carbon::today()->toDateString();

        // تحقق من الحضور المسبق
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $today)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'الموظف سجل حضوره اليوم بالفعل'], 400);
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'attendance_date' => $today,
            'check_in' => $request->check_in,
            'status' => 'present'
        ]);

        return response()->json(['message' => 'تم تسجيل الحضور بنجاح', 'data' => $attendance], 200);
    }

    // تسجيل انصراف
    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_out' => 'required'
        ]);

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'لم يتم تسجيل حضور اليوم'], 404);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'تم تسجيل الانصراف مسبقاً'], 400);
        }

        $attendance->update(['check_out' => $request->check_out]);

        return response()->json(['message' => 'تم تسجيل الانصراف بنجاح', 'data' => $attendance], 200);
    }
}
