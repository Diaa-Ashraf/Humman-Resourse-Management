<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class VisitController extends Controller
{
    // index
    public function index()
    {
        $visits = Visit::with('salesperson', 'lead', 'customer', 'industry')->latest()->get();
        return view('dashboard.visits.index', compact('visits'));
    }

    // create
    public function create(Request $request)
    {
        $user = Auth::user();

        // تحديد مندوبي المبيعات للعمل مع select فقط للأدمن
        $allSales = Employee::whereHas('section', fn($q) => $q->where('name', 'Sales'))->get();
        $customers = Customer::all();

        if ($user->hasRole('Sales')) {
            // مندوب المبيعات يرى Leads الخاصة به فقط
            $salesperson = $user->employee;
            $leads = Lead::where('sales_id', $salesperson->id)->get();
        } else {
            $salesperson = null;
            $leads = Lead::all();
        }

        $selectedLead = $request->lead_id ? Lead::find($request->lead_id) : null;

        return view('dashboard.visits.create', compact('salesperson', 'allSales', 'leads', 'customers', 'selectedLead', 'user'));
    }

    // store
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'sales_id'     => 'nullable|exists:employees,id',
            'lead_id'      => 'nullable|exists:leads,id',
            'customer_id'  => 'nullable|exists:customers,id',
            'visit_date'   => 'required|date',
            'visit_time'   => 'nullable',
            'location'     => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:Pending,Successful,Failed',
        ]);

        // ضبط مندوب المبيعات تلقائيًا إذا كان sales
        if ($user->hasRole('Sales')) {
            $validated['sales_id'] = $user->employee->id;
        }

        // ضبط التخصص تلقائيًا
        if ($validated['lead_id']) {
            $validated['industry_id'] = Lead::find($validated['lead_id'])->industry_id;
        } elseif ($validated['customer_id']) {
            $validated['industry_id'] = Customer::find($validated['customer_id'])->industry_id;
        }

        $visit = Visit::create($validated);

        // تحويل Lead → Customer تلقائيًا لو مفيش customer_id
        if ($visit->lead_id && !$visit->customer_id) {
            $lead = Lead::find($visit->lead_id);

            $customer = Customer::create([
                'name'        => $lead->name,
                'phone'       => $lead->phone,
                'email'       => $lead->email,
                'address'     => $lead->address,
                'industry_id' => $lead->industry_id,
                'sales_id'    => $lead->sales_id,
                'status'      => 'Waiting Confirmation',
            ]);

            $visit->customer_id = $customer->id;
            $visit->save();
        }

        return redirect()->route('visits.index')->with('success', 'تم تسجيل الزيارة بنجاح');
    }

    // show
    public function show(Visit $visit)
    {
        return view('dashboard.visits.show', compact('visit'));
    }

    // edit
    public function edit(Visit $visit)
    {
        $user = Auth::user();

        $allSales = Employee::whereHas('section', fn($q) => $q->where('name', 'Sales'))->get();
        $customers = Customer::all();

        if ($user->hasRole('Sales')) {
            $salesperson = $user->employee;
            $leads = Lead::where('sales_id', $salesperson->id)->get();
        } else {
            $salesperson = null;
            $leads = Lead::all();
        }

        return view('dashboard.visits.edit', compact('visit', 'salesperson', 'allSales', 'leads', 'customers', 'user'));
    }

    // update
    public function update(Request $request, Visit $visit)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'sales_id'     => 'nullable|exists:employees,id',
            'lead_id'      => 'nullable|exists:leads,id',
            'customer_id'  => 'nullable|exists:customers,id',
            'visit_date'   => 'required|date',
            'visit_time'   => 'nullable',
            'location'     => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:Pending,Successful,Failed',
        ]);

        if ($user->hasRole('Sales')) {
            $validated['sales_id'] = $user->employee->id;
        }

        if ($validated['lead_id']) {
            $validated['industry_id'] = Lead::find($validated['lead_id'])->industry_id;
        } elseif ($validated['customer_id']) {
            $validated['industry_id'] = Customer::find($validated['customer_id'])->industry_id;
        }

        $visit->update($validated);

        return redirect()->route('visits.index')->with('success', 'تم تحديث الزيارة بنجاح');
    }

    // destroy
    public function destroy(Visit $visit)
    {
        $visit->delete();
        return redirect()->route('visits.index')->with('success', 'تم حذف الزيارة بنجاح');
    }
}
