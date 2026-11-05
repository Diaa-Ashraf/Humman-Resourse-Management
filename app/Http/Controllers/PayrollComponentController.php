<?php
namespace App\Http\Controllers;
use App\Models\{Employee,EmployeeSalaryComponent,SalaryComponent};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BranchAccess;

class PayrollComponentController extends Controller
{
    private function authorizeManager():void{abort_unless(auth()->user()->can('manage_salary_components'),403);}
    public function index()
    {
        $this->authorizeManager();
        $components=SalaryComponent::withCount('employeeAmounts')->orderBy('type')->orderBy('name')->get();
        $access=app(BranchAccess::class);$assignments=$access->throughEmployee(EmployeeSalaryComponent::query(),auth()->user())->with(['employee.branch','component'])->latest('effective_from')->paginate(20);
        $employees=$access->employees(Employee::query(),auth()->user())->where('status','active')->orderBy('name')->get();
        return view('dashboard.salary-components.index',compact('components','assignments','employees'));
    }
    public function store(Request $request)
    {
        $this->authorizeManager();
        $data=$request->validate(['code'=>'required|string|max:40|alpha_dash|unique:salary_components,code','name'=>'required|string|max:120','type'=>'required|in:earning,deduction','taxable'=>'required|boolean','insurable'=>'required|boolean','recurring'=>'required|boolean']);
        SalaryComponent::create($data+['is_active'=>true]);return back()->with('success','تم إنشاء مكون الراتب.');
    }
    public function assign(Request $request)
    {
        $this->authorizeManager();
        $data=$request->validate(['employee_id'=>'required|exists:employees,id','salary_component_id'=>['required',Rule::exists('salary_components','id')->where('is_active',true)],'amount'=>'required|numeric|min:0.01|max:99999999','effective_from'=>'required|date','effective_to'=>'nullable|date|after_or_equal:effective_from']);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());
        $overlap=EmployeeSalaryComponent::where('employee_id',$data['employee_id'])->where('salary_component_id',$data['salary_component_id'])->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$data['effective_from']))->when($data['effective_to'],fn($q)=>$q->whereDate('effective_from','<=',$data['effective_to']))->exists();
        if($overlap)return back()->withInput()->withErrors(['salary_component_id'=>'يوجد مكون متداخل المدة للموظف.']);
        EmployeeSalaryComponent::create($data);return back()->with('success','تم إسناد المكون للموظف بتاريخ سريان محدد.');
    }
    public function remove(EmployeeSalaryComponent $assignment)
    {
        $this->authorizeManager();
        app(BranchAccess::class)->assertEmployee($assignment->employee,auth()->user());
        $assignment->update(['effective_to'=>today()->subDay()->toDateString()]);return back()->with('success','تم إنهاء سريان مكون الراتب.');
    }
}
