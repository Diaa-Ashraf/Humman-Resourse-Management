<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\Bonus;
use App\Models\Deduction;
use Illuminate\Validation\Rule;
use App\Models\AdvanceInstallment;
use Illuminate\Support\Facades\DB;
use App\Services\BranchAccess;
class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_salaries')->only(['index', 'show']);
        $this->middleware('permission:store_salaries')->only(['store', 'create']);
        $this->middleware('permission:update_salaries')->only(['update', 'edit']);
        $this->middleware('permission:delete_salaries')->only(['destroy']);
        $this->middleware('permission:pay_salaries')->only(['markPaid']);
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('show_salaries') || auth()->user()->can('pay_salaries'), 403);
        $query = app(BranchAccess::class)->throughEmployee(Salary::query(), auth()->user());

        if ($request->filled('month') !== $request->filled('year')) {
            return back()->withInput()->withErrors(['period' => 'اختر الشهر والسنة معًا لتصفية مسيرات الرواتب.']);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $request->validate(['month' => 'integer|between:1,12', 'year' => 'integer|between:2000,2100']);
            $query->whereMonth('payment_date', $request->month)
                ->whereYear('payment_date', $request->year);
        }

        if (!$request->filled('month') && !$request->filled('year')) {
            $query->whereMonth('payment_date', Carbon::now()->month)->whereYear('payment_date', Carbon::now()->year);
        }

        $salaries = $query->with('employee')->latest('payment_date')->paginate(20)->withQueryString();

        return view('dashboard.salaries.index', compact('salaries'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('store_salaries'), 403);
        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->select('id', 'name', 'base_salary')->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.salaries.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('status', 'active')],
            'payment_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:pending',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        if ($employee->status !== 'active') {
            return back()->withInput()->withErrors(['employee_id' => 'لا يمكن إنشاء راتب لموظف غير نشط.']);
        }

        $paymentDate = Carbon::parse($validated['payment_date']);

        $exists = Salary::where('employee_id', $employee->id)
            ->whereMonth('payment_date', $paymentDate->month)
            ->whereYear('payment_date', $paymentDate->year)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['msg' => 'يوجد راتب مسجل بالفعل لهذا الموظف في الشهر نفسه.']);
        }

        $bonuses = Bonus::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('bonus_date', $paymentDate->month)
            ->whereYear('bonus_date', $paymentDate->year)
            ->sum('amount');

        $deductions = Deduction::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('deduction_date', $paymentDate->month)
            ->whereYear('deduction_date', $paymentDate->year)
            ->sum('amount');

        $baseSalary = $employee->base_salary;
        $total = max(0, $baseSalary + $bonuses - $deductions);

        Salary::create([
            'employee_id' => $employee->id,
            'amount' => $baseSalary,
            'total' => $total,
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('salaries.index')->with('success', 'تم تجهيز مسير الراتب للمراجعة.');
    }

    public function show(Salary $salary)
    {
        app(BranchAccess::class)->assertEmployee($salary->employee,auth()->user());
        $salary->load('employee');
        return view('dashboard.salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        app(BranchAccess::class)->assertEmployee($salary->employee,auth()->user());
        abort_unless(auth()->user()->can('update_salaries'), 403);
        abort_if($salary->payroll_run_id, 403, 'رواتب المسير الشهري تُراجع من خلال دورة الرواتب ولا تعدّل منفردة.');
        if ($salary->status === 'paid') {
            abort(403, 'لا يمكن تعديل راتب تم صرفه.');
        }

        $employees = app(BranchAccess::class)->employees(Employee::query(),auth()->user())->where('status', 'active')->orderBy('name')->get();
        return view('dashboard.salaries.edit', compact('salary', 'employees'));
    }

    public function update(Request $request, Salary $salary)
    {
        app(BranchAccess::class)->assertEmployee($salary->employee,auth()->user());
        abort_unless(auth()->user()->can('update_salaries'), 403);
        abort_if($salary->payroll_run_id, 403, 'رواتب المسير الشهري تُراجع من خلال دورة الرواتب ولا تعدّل منفردة.');
        if ($salary->status === 'paid') {
            abort(403, 'لا يمكن تعديل راتب تم صرفه.');
        }
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where(fn($query) => $query->where('status', 'active')->orWhere('id', $salary->employee_id))],
            'payment_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:pending',
        ]);

        $paymentDate = Carbon::parse($validated['payment_date']);
        $employeeId = $validated['employee_id'];

        $exists = Salary::where('employee_id', $employeeId)
            ->whereMonth('payment_date', $paymentDate->month)
            ->whereYear('payment_date', $paymentDate->year)
            ->where('id', '!=', $salary->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['msg' => 'يوجد راتب مسجل بالفعل لهذا الموظف في الشهر نفسه.']);
        }

        $employee = Employee::findOrFail($employeeId);
        app(BranchAccess::class)->assertEmployee($employee,auth()->user());
        $baseSalary = $employee->base_salary;

        if ($employee->status !== 'active') {
            return back()->withInput()->withErrors(['employee_id' => 'لا يمكن صرف راتب لموظف غير نشط.']);
        }

        $approvedBonuses = Bonus::where('employee_id', $employeeId)->where('status', 'approved')
            ->whereMonth('bonus_date', $paymentDate->month)->whereYear('bonus_date', $paymentDate->year)->sum('amount');
        $approvedDeductions = Deduction::where('employee_id', $employeeId)->where('status', 'approved')
            ->whereMonth('deduction_date', $paymentDate->month)->whereYear('deduction_date', $paymentDate->year)->sum('amount');
        $total = max(0, $baseSalary + $approvedBonuses - $approvedDeductions);

        $salary->update([
            'employee_id' => $employeeId,
            'amount' => $baseSalary,
            'total' => $total,
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('salaries.index')->with('success', 'تم تحديث بيانات الراتب بنجاح.');
    }

    public function destroy(Salary $salary)
    {
        app(BranchAccess::class)->assertEmployee($salary->employee,auth()->user());
        abort_unless(auth()->user()->can('delete_salaries'), 403);
        abort_if($salary->payroll_run_id, 403, 'لا يمكن حذف راتب مرتبط بمسير شهري.');
        if ($salary->status === 'paid') {
            return back()->withErrors(['msg' => 'لا يمكن حذف راتب تم صرفه.']);
        }

        $salary->delete();

        return redirect()->route('salaries.index')->with('success', 'تم حذف مسير الراتب بنجاح.');
    }

    public function markPaid(Salary $salary)
    {
        app(BranchAccess::class)->assertEmployee($salary->employee,auth()->user());
        abort_unless(auth()->user()->can('pay_salaries'), 403, 'لا يمكن صرف الرواتب دون صلاحية الصرف.');
        if ($salary->status !== 'pending') {
            return back()->withErrors(['msg' => 'لا يمكن تغيير حالة هذا المسير؛ فهو ليس قيد الانتظار.']);
        }
        if($salary->payroll_run_id && $salary->payrollRun?->status!=='closed') return back()->withErrors(['msg'=>'لا يمكن صرف راتب قبل إقفال المسير واعتماده.']);
        DB::transaction(function()use($salary){
            $salary->update(['status'=>'paid','paid_at'=>now(),'paid_by'=>auth()->id()]);
            foreach($salary->calculation_snapshot['advance_installment_ids']??[] as $installmentId){
                $installment=AdvanceInstallment::with('advance')->lockForUpdate()->find($installmentId);
                if(!$installment||$installment->status!=='scheduled')continue;
                $installment->update(['status'=>'paid','paid_date'=>today(),'salary_id'=>$salary->id]);
                $advance=$installment->advance;$advance->balance=max(0,(float)$advance->balance-(float)$installment->amount);
                if($advance->balance<=0.009)$advance->update(['balance'=>0,'status'=>'repaid']);else $advance->save();
            }
        });

        return redirect()->route('salaries.index')->with('success', 'تم تأكيد صرف الراتب.');
    }
}
