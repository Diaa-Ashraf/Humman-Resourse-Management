<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\BranchAccess;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_attendances')->only(['index']);
        $this->middleware('permission:store_attendances')->only(['checkIn', 'checkOut']);
        $this->middleware('permission:import_attendances')->only(['importCsv']);
        $this->middleware('permission:review_attendances')->only(['reviewImport']);
    }
    // عرض صفحة الحضور
    public function index(Request $request)
    {
        $access=app(BranchAccess::class);$query = $access->throughEmployee(Attendance::query(),auth()->user())->with('employee.section', 'employee.branch')->orderByDesc('attendance_date')->orderByDesc('check_in');

        // الفلاتر
        $validated=$request->validate(['date' => 'nullable|date', 'status' => 'nullable|in:present,absent,late,leave', 'section' => 'nullable|integer|exists:sections,id', 'branch' => 'nullable|integer|exists:branches,id']);
        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $query->whereDate('attendance_date', $date);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->section) {
            $query->whereHas('employee', fn($q) => $q->where('section_id', $request->section));
        }

        if ($request->branch) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $request->branch));
        }

        $summaryQuery = clone $query;
        $attendanceSummary = [
            'present' => (clone $summaryQuery)->whereIn('status', ['present', 'late'])->count(),
            'absent' => (clone $summaryQuery)->where('status', 'absent')->count(),
            'late' => (clone $summaryQuery)->where('status', 'late')->count(),
            'leave' => (clone $summaryQuery)->where('status', 'leave')->count(),
        ];
        $attendances = $query->paginate(15)->withQueryString();
        if($request->filled('branch'))$access->assertBranch((int)$request->branch,auth()->user());
        $employees = $access->employees(Employee::query(),auth()->user())->with(['section', 'branch'])->where('status', 'active')->when($request->filled('branch'), fn($q) => $q->where('branch_id', $request->branch))->when($request->filled('section'), fn($q) => $q->where('section_id', $request->section))->orderBy('name')->get();
        $sections = Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->orderBy('name')->get();
        $branches = \App\Models\Branch::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->orderBy('name')->get();

        return view('dashboard.attendance.index', compact('attendances', 'employees', 'sections', 'branches', 'attendanceSummary'));
    }

    // تسجيل حضور
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'check_in' => 'required|date_format:H:i'
        ]);

        $today = Carbon::today()->toDateString();

        // تحقق من الحضور المسبق
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $today)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'الموظف سجل حضوره اليوم بالفعل'], 400);
        }

        $employee = Employee::with('shift')->findOrFail($validated['employee_id']);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $shift = \App\Models\ShiftAssignment::with('shift')
            ->where('employee_id', $employee->id)->whereDate('work_date', $today)
            ->first()?->shift ?? $employee->shift;
        $lateMinutes = 0;
        if ($shift) {
            $scheduled = Carbon::parse($today.' '.$shift->start_time);
            $actual = Carbon::parse($today.' '.$validated['check_in']);
            // A late-night shift can start shortly before midnight; interpret early
            // morning clock-ins as belonging to that shift's next day.
            if ($shift->crosses_midnight && $actual->lt($scheduled) && $scheduled->diffInHours($actual, false) < -12) $actual->addDay();
            $lateMinutes = max(0, (int) floor(($actual->timestamp - $scheduled->timestamp) / 60) - (int) $shift->grace_minutes);
        }
        $status = $lateMinutes > 0 ? 'late' : 'present';
        $attendance = Attendance::create([
            'employee_id' => $validated['employee_id'],
            'attendance_date' => $today,
            'check_in' => $validated['check_in'],
            'status' => $status,'shift_id'=>$shift?->id,'late_minutes'=>$lateMinutes,'source'=>'manual','review_status'=>'approved','reviewed_by'=>auth()->id()
        ]);

        return response()->json(['message' => 'تم تسجيل الحضور بنجاح', 'data' => $attendance], 200);
    }

    // تسجيل انصراف
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'check_out' => 'required|date_format:H:i'
        ]);

        $today = Carbon::today()->toDateString();

        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($validated['employee_id']),auth()->user());
        $attendance = Attendance::with('shift')->where('employee_id', $validated['employee_id'])
            ->where(function ($query) use ($today) {
                $query->whereDate('attendance_date', $today)
                    ->orWhere(function ($previousDay) use ($today) {
                        $previousDay->whereDate('attendance_date', Carbon::parse($today)->subDay()->toDateString())
                            ->whereHas('shift', fn ($shift) => $shift->where('crosses_midnight', true));
                    });
            })
            ->whereNull('check_out')->orderByDesc('attendance_date')->first();

        if (!$attendance) {
            return response()->json(['message' => 'لم يتم تسجيل حضور اليوم'], 404);
        }

        $overtimeMinutes = (int) $attendance->overtime_minutes;
        if ($attendance->shift) {
            $shift = $attendance->shift;
            $workDate = Carbon::parse($attendance->attendance_date)->toDateString();
            $scheduledEnd = Carbon::parse($workDate.' '.$shift->end_time);
            if ($shift->crosses_midnight && Carbon::parse($shift->end_time)->lt(Carbon::parse($shift->start_time))) $scheduledEnd->addDay();
            $actualEnd = Carbon::parse($workDate.' '.$validated['check_out']);
            if ($shift->crosses_midnight && Carbon::parse($validated['check_out'])->lt(Carbon::parse($shift->start_time))) $actualEnd->addDay();
            $overtimeMinutes = max($overtimeMinutes, max(0, (int) floor(($actualEnd->timestamp - $scheduledEnd->timestamp) / 60)));
        }
        $attendance->update(['check_out' => $validated['check_out'], 'overtime_minutes' => $overtimeMinutes]);

        return response()->json(['message' => 'تم تسجيل الانصراف بنجاح', 'data' => $attendance], 200);
    }

    public function importCsv(Request $request)
    {
        abort_unless(auth()->user()->can('import_attendances'),403);
        $request->validate(['file'=>'required|file|mimes:csv,txt|max:10240']);$handle=fopen($request->file('file')->getRealPath(),'r');$headers=fgetcsv($handle);
        if(!$headers){fclose($handle);return back()->withErrors(['file'=>'ملف الحضور فارغ.']);}
        $headers=array_map(fn($h)=>trim(preg_replace('/^\xEF\xBB\xBF/','',(string)$h)),$headers);$required=['employee_number','attendance_date','status'];
        if(array_diff($required,$headers)){fclose($handle);return back()->withErrors(['file'=>'الأعمدة الإلزامية: employee_number,attendance_date,status.']);}
        $created=0;$errors=[];$rowNumber=1;
        while(($values=fgetcsv($handle))!==false){$rowNumber++;if(!array_filter($values))continue;$row=array_combine($headers,array_pad(array_slice($values,0,count($headers)),count($headers),''));
            $check=validator($row,['employee_number'=>'required|string|max:40','attendance_date'=>'required|date_format:Y-m-d','check_in'=>'nullable|date_format:H:i','check_out'=>'nullable|date_format:H:i','status'=>'required|in:present,absent,late,leave','overtime_minutes'=>'nullable|integer|between:0,1440','notes'=>'nullable|string|max:500']);
            if($check->fails()){$errors[]="السطر {$rowNumber}: ".$check->errors()->first();continue;}
            $employee=Employee::where('employee_number',$row['employee_number'])->where('status','active')->first();if(!$employee){$errors[]="السطر {$rowNumber}: الرقم الوظيفي غير موجود أو غير نشط.";continue;}
            try{app(BranchAccess::class)->assertEmployee($employee,auth()->user());}catch(\Symfony\Component\HttpKernel\Exception\HttpException){$errors[]="السطر {$rowNumber}: الموظف خارج نطاق فروعك.";continue;}
            if(Attendance::where('employee_id',$employee->id)->whereDate('attendance_date',$row['attendance_date'])->exists()){$errors[]="السطر {$rowNumber}: يوجد سجل سابق للموظف في هذا اليوم.";continue;}
            $shift=\App\Models\ShiftAssignment::with('shift')->where('employee_id',$employee->id)->whereDate('work_date',$row['attendance_date'])->first()?->shift ?? $employee->shift;
            $lateMinutes=0;if($row['check_in']&&$shift){$scheduled=Carbon::parse($row['attendance_date'].' '.$shift->start_time);$actual=Carbon::parse($row['attendance_date'].' '.$row['check_in']);if($shift->crosses_midnight&&$actual->lt($scheduled)&&$scheduled->diffInHours($actual,false)<-12)$actual->addDay();$lateMinutes=max(0,(int)floor(($actual->timestamp-$scheduled->timestamp)/60)-(int)$shift->grace_minutes);}
            try{DB::transaction(fn()=>Attendance::create(['employee_id'=>$employee->id,'attendance_date'=>$row['attendance_date'],'check_in'=>$row['check_in']?:null,'check_out'=>$row['check_out']?:null,'status'=>$row['status'],'shift_id'=>$shift?->id,'late_minutes'=>$lateMinutes,'overtime_minutes'=>(int)($row['overtime_minutes']??0),'source'=>'csv','review_status'=>'pending','review_notes'=>$row['notes']??null]));$created++;}catch(\Throwable){$errors[]="السطر {$rowNumber}: تعذر حفظ السجل.";}
        }
        fclose($handle);return back()->with('attendance_import',['created'=>$created,'errors'=>array_slice($errors,0,50),'error_count'=>count($errors)]);
    }

    public function reviewImport(Request $request,Attendance $attendance)
    {
        app(BranchAccess::class)->assertEmployee($attendance->employee,auth()->user());
        abort_unless(auth()->user()->can('review_attendances'),403);abort_unless($attendance->review_status==='pending',409);
        $data=$request->validate(['decision'=>'required|in:approved,rejected','review_notes'=>'nullable|string|max:1000']);
        $attendance->update(['review_status'=>$data['decision'],'reviewed_by'=>auth()->id(),'review_notes'=>$data['review_notes']??$attendance->review_notes]);
        return back()->with('success',$data['decision']==='approved'?'تم اعتماد سجل الحضور.':'تم رفض السجل واستبعاده من المسير.');
    }
}
