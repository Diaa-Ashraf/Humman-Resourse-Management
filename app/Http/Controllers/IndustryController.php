<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_industries')->only(['index', 'show']);
        $this->middleware('permission:store_industries')->only(['create', 'store']);
        $this->middleware('permission:update_industries')->only(['edit', 'update']);
        $this->middleware('permission:delete_industries')->only(['destroy']);
    }
    // index
    public function index()
    {
        $industries = Industry::all();
        return view('dashboard.industries.index', compact('industries'));
    }

    // create
    public function create()
    {
        return view('dashboard.industries.create');
    }

    // store
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:industries,name',
        ]);

        Industry::create($validatedData);

        return redirect()->route('industry.index')->with('success', 'تم إضافة التخصص بنجاح.');
    }

    // // show
    // public function show(Industry $industry)
    // {
    //     return view('dashboard.industry.show', compact('industry'));
    // }

    // edit
    public function edit(Industry $industry)
    {
        return view('dashboard.industries.edit', compact('industry'));
    }

    // update
    public function update(Request $request, Industry $industry)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:industries,name,' . $industry->id,
        ]);

        $industry->update($validatedData);

        return redirect()->route('industry.index')->with('success', 'تم تحديث التخصص بنجاح.');
    }

    // destroy
    public function destroy(Industry $industry)
    {
        $industry->delete();
        return redirect()->route('industry.index')->with('success', 'تم حذف التخصص بنجاح.');
    }
}
