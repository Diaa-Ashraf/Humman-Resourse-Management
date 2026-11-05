<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Industry;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_leads')->only(['index', 'show']);
        $this->middleware('permission:store_leads')->only(['create', 'store']);
        $this->middleware('permission:update_leads')->only(['edit', 'update']);
        $this->middleware('permission:delete_leads')->only(['destroy']);
    }
    // start function index
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Sales')) {
            // المندوب يشوف فقط الـ Leads الخاصة به
            $leads = Lead::where('sales_id', $user->employee->id)
                ->with('industry', 'customer', 'salesperson')
                ->get();
        } else {
            // المدير أو HR يشوف الكل
            $leads = Lead::with('industry', 'customer', 'salesperson')->get();
        }

        return view('dashboard.leads.index', compact('leads'));
    }
    // end function index


    // start function create
    public function create()
    {
        $industries = Industry::all();

        // فقط الموظفين في قسم Sales
        $employees = Employee::whereHas('section', function ($q) {
            $q->where('name', 'Sales');
        })->get();

        return view('dashboard.leads.create', compact('industries', 'employees'));
    }
    // end function create

    // start function store
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'industry_id' => 'nullable|exists:industries,id',
            'sales_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if (!Employee::where('id', $value)
                        ->whereHas('section', fn($q) => $q->where('name', 'sales'))
                        ->exists()) {
                        $fail('الموظف المختار ليس مندوب مبيعات.');
                    }
                }
            ],
            'source' => 'required|in:Facebook,Website,Phone,Visit,Other',
            'status' => 'required|in:New,In Progress,Hot,Converted,Lost',
        ]);

        $lead = Lead::create($validatedData);

        // تحويل تلقائي للعميل لو تم تحويل Lead
        if ($lead->status === 'Converted') {
            Customer::create([
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'address' => $lead->address,
                'industry_id' => $lead->industry_id,
                'sales_id' => $lead->sales_id,
                'status' => 'Active',
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'تم إضافة العميل المحتمل بنجاح.');
    }
    // end function store

    // start function show
    public function show(Lead $lead)
    {
        $industries = Industry::all();

        $employees = Employee::whereHas('section', function ($q) {
            $q->where('name', 'Sales');
        })->get();

        return view('dashboard.leads.show', compact('lead', 'industries', 'employees'));
    }
    // end function show

    // start function edit
    public function edit(Lead $lead)
    {
        $industries = Industry::all();

        $employees = Employee::whereHas('section', function ($q) {
            $q->where('name', 'Sales');
        })->get();

        return view('dashboard.leads.edit', compact('lead', 'industries', 'employees'));
    }
    // end function edit

    // start function update
    public function update(Request $request, Lead $lead)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'industry_id' => 'nullable|exists:industries,id',
            'sales_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if (!Employee::where('id', $value)
                        ->whereHas('section', fn($q) => $q->where('name', 'sales'))
                        ->exists()) {
                        $fail('الموظف المختار ليس مندوب مبيعات.');
                    }
                }
            ],
            'source' => 'required|in:Facebook,Website,Phone,Visit,Other',
            'status' => 'required|in:New,In Progress,Hot,Converted,Lost',
        ]);

        $lead->update($validatedData);

        // تحويل تلقائي للعميل لو تم تحويل Lead
        if ($lead->status === 'Converted' && !$lead->customer_id) {
            $customer = Customer::create([
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'address' => $lead->address,
                'industry_id' => $lead->industry_id,
                'sales_id' => $lead->sales_id,
                'status' => 'Active',
            ]);

            $lead->customer_id = $customer->id;
            $lead->save();
        }

        return redirect()->route('leads.index')->with('success', 'تم تحديث العميل المحتمل بنجاح.');
    }
    // end function update

    // start function destroy
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'تم حذف العميل المحتمل بنجاح.');
    }
    // end function destroy
}
