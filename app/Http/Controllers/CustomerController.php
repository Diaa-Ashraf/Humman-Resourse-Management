<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Industry;
use Illuminate\Http\Request;
use App\Http\Requests\customer\StoreCustomerRequest;
use App\Http\Requests\customer\UpdateCustomerRequest;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_customers')->only(['index', 'show']);
        $this->middleware('permission:store_customers')->only(['create', 'store']);
        $this->middleware('permission:update_customers')->only(['edit', 'update']);
        $this->middleware('permission:delete_customers')->only(['destroy']);
    }

    // public function __construct()
    // {
    //     return[
    //         new middleear ('permission:show_customers')->only(['index', 'show']),
    //         new middleear ('permission:store_customers')->only(['create', 'store']),
    //         new middleear ('permission:update_customers')->only(['edit', 'update']),
    //         new middleear ('permission:delete_customers')->only(['destroy']),
    //     ]
        // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = cache()->remember('customers.list', 60, function () {
            return Customer::with('industry')->get();
        });

        return view('dashboard.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $industries = cache()->remember('industries', 60, function () {
            return Industry::select('id', 'name')->get();
        });
        return view('dashboard.customers.create', compact('industries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect()->route('customers.index')
            ->with('success', 'تم إضافة العميل بنجاح');
    }


    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load('industry', 'visits');
        return view('dashboard.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $industries = Industry::select('id', 'name')->get();
        return view('dashboard.customers.edit', compact('customer', 'industries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        cache()->forget('industries.list'); // Clear the cache for industries list

        return redirect()->route('customers.index')
            ->with('success', 'تم تحديث العميل بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        cache()->forget('industries.list'); // Clear the cache for industries list
        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }
}
