<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BranchAccess;

class BranchController extends Controller
{
    private const BRANCH_LIMIT = 20;
    public function __construct()
    {
        $this->middleware('permission:show_branches')->only('index');
        $this->middleware('permission:store_branches')->only(['create', 'store']);
        $this->middleware('permission:update_branches')->only(['edit', 'update']);
        $this->middleware('permission:delete_branches')->only('destroy');
    }

    public function index()
    {
        $access=app(BranchAccess::class);$query=Branch::query()->when(!$access->isGlobal(auth()->user()),fn($q)=>$q->whereIn('id',$access->branchIds(auth()->user())));
        $branches = $query->withCount('employees')->orderBy('name')->paginate(15)->withQueryString();
        $branchCount = (clone $query)->count();
        return view('dashboard.branches.index', compact('branches', 'branchCount'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('store_branches'), 403);
        abort_if(Branch::count() >= self::BRANCH_LIMIT, 403, 'وصل النظام إلى الحد المحدد وهو 20 فرعًا.');
        return view('dashboard.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'code' => ['required', 'string', 'max:20', 'unique:branches,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);
        if (Branch::count() >= self::BRANCH_LIMIT) {
            return back()->withInput()->withErrors(['branches' => 'وصل النظام إلى الحد المحدد وهو 20 فرعًا.']);
        }
        Branch::create($data);
        return redirect()->route('branches.index')->with('success', 'تم إضافة الفرع بنجاح.');
    }

    public function edit(Branch $branch)
    {
        abort_unless(auth()->user()->can('update_branches'), 403);
        app(BranchAccess::class)->assertBranch((int)$branch->id,auth()->user());
        return view('dashboard.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        abort_unless(auth()->user()->can('update_branches'), 403);
        app(BranchAccess::class)->assertBranch((int)$branch->id,auth()->user());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->ignore($branch->id)],
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($branch->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);
        $branch->update($data);
        return redirect()->route('branches.index')->with('success', 'تم تحديث الفرع بنجاح.');
    }

    public function destroy(Branch $branch)
    {
        abort_unless(auth()->user()->can('delete_branches'), 403);
        app(BranchAccess::class)->assertBranch((int)$branch->id,auth()->user());
        if ($branch->employees()->exists()) {
            return back()->withErrors(['branch' => 'لا يمكن حذف فرع مرتبط بموظفين.']);
        }
        if ($branch->is_active) {
            return back()->withErrors(['branch' => 'يرجى إيقاف الفرع قبل حذفه.']);
        }
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'تم حذف الفرع بنجاح.');
    }
}
