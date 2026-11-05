<?php
namespace App\Http\Controllers;
use App\Models\{Branch,CompanyHoliday};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CompanyHolidayController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);$holidays=CompanyHoliday::with('branch')->orderBy('holiday_date')->paginate(25);$branches=Branch::orderBy('name')->get();return view('dashboard.leave-balances.holidays',compact('holidays','branches'));
    }
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);$data=$request->validate(['name'=>'required|string|max:150','holiday_date'=>'required|date','is_recurring'=>'required|boolean','scope'=>'required|in:company,branch','branch_id'=>['nullable',Rule::requiredIf($request->input('scope')==='branch'),'exists:branches,id']]);
        if(CompanyHoliday::whereDate('holiday_date',$data['holiday_date'])->where('branch_id',$data['branch_id']??null)->exists())return back()->withInput()->withErrors(['holiday_date'=>'يوجد سجل عطلة لهذا التاريخ والنطاق.']);
        CompanyHoliday::create($data);return back()->with('success','تم إضافة العطلة إلى التقويم.');
    }
    public function destroy(CompanyHoliday $holiday)
    {
        abort_unless(auth()->user()->can('manage_leave_balances'),403);$holiday->delete();return back()->with('success','تم حذف العطلة.');
    }
}
