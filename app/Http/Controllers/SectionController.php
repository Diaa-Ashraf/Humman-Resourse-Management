<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_sections')->only(['index', 'show']);
        $this->middleware('permission:store_sections')->only(['create', 'store']);
        $this->middleware('permission:update_sections')->only(['edit', 'update']);
        $this->middleware('permission:delete_sections')->only(['destroy']);
    }

    // صفحة عرض كل الأقسام
    public function index()
    {
        $sections = Section::paginate(10);
        return view('dashboard.sections.index', compact('sections'));
    }

    // صفحة إنشاء قسم جديد
    public function create()
    {
        return view('dashboard.sections.create');
    }

    // حفظ القسم
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Section::create($validated);

        return redirect()->route('sections.index')->with('success', 'تم إنشاء القسم بنجاح');
    }

    // صفحة تعديل القسم
    public function edit(Section $section)
    {
        return view('dashboard.sections.edit', compact('section'));
    }

    // تحديث القسم
    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update($validated);

        return redirect()->route('sections.index')->with('success', 'تم تعديل القسم بنجاح');
    }

    // حذف القسم
    public function destroy(Section $section)
    {
        $section->delete();
        return redirect()->route('sections.index')->with('success', 'تم حذف القسم بنجاح');
    }
}
