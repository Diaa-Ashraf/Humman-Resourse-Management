<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Branch;
use Illuminate\Validation\Rule;
use App\Services\BranchAccess;
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
        $access=app(BranchAccess::class);$sections = Section::when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('branch_id',$access->branchIds(auth()->user())))->with(['branch'])->withCount('employees')->orderBy('name')->paginate(20);
        return view('dashboard.sections.index', compact('sections'));
    }

    // صفحة إنشاء قسم جديد
    public function create()
    {
        abort_unless(auth()->user()->can('store_sections'), 403);
        $access=app(BranchAccess::class);$branches = Branch::where('is_active',true)->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())))->orderBy('name')->get();
        return view('dashboard.sections.create',compact('branches'));
    }

    // حفظ القسم
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections,name',
            'code'=>'nullable|string|max:30|unique:sections,code','branch_id'=>'nullable|exists:branches,id','is_active'=>'required|boolean',
        ]);
        app(BranchAccess::class)->assertBranch(isset($validated['branch_id'])?(int)$validated['branch_id']:null,auth()->user());

        Section::create($validated);

        return redirect()->route('sections.index')->with('success', 'تم إنشاء القسم بنجاح');
    }

    // صفحة تعديل القسم
    public function edit(Section $section)
    {
        abort_unless(auth()->user()->can('update_sections'), 403);
        app(BranchAccess::class)->assertBranch((int)$section->branch_id,auth()->user());$access=app(BranchAccess::class);$branches = Branch::where(fn($q)=>$q->where('is_active',true)->when(!$access->isGlobal(auth()->user()),fn($x)=>$x->whereIn('id',$access->branchIds(auth()->user())))->orWhere('id',$section->branch_id))->orderBy('name')->get();
        return view('dashboard.sections.edit', compact('section','branches'));
    }

    // تحديث القسم
    public function update(Request $request, Section $section)
    {
        app(BranchAccess::class)->assertBranch((int)$section->branch_id,auth()->user());
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections,name,' . $section->id,
            'code'=>['nullable','string','max:30',Rule::unique('sections','code')->ignore($section->id)],'branch_id'=>'nullable|exists:branches,id','is_active'=>'required|boolean',
        ]);
        app(BranchAccess::class)->assertBranch(isset($validated['branch_id'])?(int)$validated['branch_id']:null,auth()->user());

        $section->update($validated);

        return redirect()->route('sections.index')->with('success', 'تم تعديل القسم بنجاح');
    }

    // حذف القسم
    public function destroy(Section $section)
    {
        abort_unless(auth()->user()->can('delete_sections'), 403);
        app(BranchAccess::class)->assertBranch((int)$section->branch_id,auth()->user());
        if ($section->employees()->exists()) {
            return back()->withErrors(['section' => 'لا يمكن حذف قسم مرتبط بموظفين.']);
        }
        $section->delete();
        return redirect()->route('sections.index')->with('success', 'تم حذف القسم بنجاح');
    }
}
