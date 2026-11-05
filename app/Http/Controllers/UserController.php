<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:show_users')->only(['index', 'show']);
        $this->middleware('permission:store_users')->only(['create', 'store']);
        $this->middleware('permission:update_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
    }

    public function index()
    {
        abort_unless(auth()->user()->can('show_users') || auth()->user()->can('assign_roles'), 403);
        $users = User::with(['roles', 'branchScopes'])->paginate(10);
        return view('dashboard.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeUserAdministration();
        abort_if(User::count() >= 10, 403, 'وصل النظام إلى الحد الأقصى وهو 10 مستخدمين.');
        $roles = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('dashboard.users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorizeUserAdministration();
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string|exists:roles,name',
            'branch_scopes' => 'nullable|array',
            'branch_scopes.*' => 'integer|distinct|exists:branches,id,is_active,1',
        ]);
        $this->validateScopesForRoles($validated['roles'], $validated['branch_scopes'] ?? []);

        if (User::count() >= 10) {
            return back()->withInput()->withErrors(['users' => 'وصل النظام إلى الحد الأقصى وهو 10 مستخدمين.']);
        }

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles($validated['roles']);
        $user->forceFill(['password_changed_at' => now()])->save();
        $this->syncScopes($user, $validated['branch_scopes'] ?? []);

        return redirect()->route('users.index')->with('success', 'تم إنشاء حساب المستخدم بنجاح.');
    }

    

    public function edit(User $user)
    {
        $this->authorizeUserAdministration();
        abort_if($user->id === auth()->id() && !auth()->user()->can('manage_users'), 403, 'إدارة حسابك الحالي تتطلب صلاحية مسؤول النظام.');
        $allRoles = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedScopes = $user->branchScopes()->pluck('branches.id')->all();
        return view('dashboard.users.edit', compact('user', 'allRoles', 'branches', 'selectedScopes'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserAdministration();
        abort_if($user->id === auth()->id() && !auth()->user()->can('manage_users'), 403, 'إدارة حسابك الحالي تتطلب صلاحية مسؤول النظام.');
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword],
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string|exists:roles,name',
            'branch_scopes' => 'nullable|array',
            'branch_scopes.*' => 'integer|distinct|exists:branches,id,is_active,1',
        ]);
        $this->validateScopesForRoles($validated['roles'], $validated['branch_scopes'] ?? []);

        if ($validated['password'] ?? false) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $roles = $validated['roles'] ?? $user->getRoleNames()->all();
        $branchScopes = $validated['branch_scopes'] ?? [];
        unset($validated['roles'], $validated['branch_scopes']);

        if (!empty($validated['password'])) {
            $user->update($validated);
            $user->forceFill(['password_changed_at' => now()])->save();
            $sessionId = $user->id === auth()->id() ? request()->session()->getId() : null;
            $user->securitySessions()->whereNull('revoked_at')->when($sessionId,fn($q)=>$q->where('session_id','!=',$sessionId))->update(['revoked_at' => now()]);
            $sessionsTable = config('session.table', 'sessions');
            if (\Illuminate\Support\Facades\Schema::hasTable($sessionsTable) && \Illuminate\Support\Facades\Schema::hasColumn($sessionsTable,'user_id')) {
                $sessionQuery=\Illuminate\Support\Facades\DB::table($sessionsTable)->where('user_id',$user->id);
                if($sessionId)$sessionQuery->where('id','!=',$sessionId);
                $sessionQuery->delete();
            }
        } else {
            $user->update($validated);
        }

        $user->syncRoles($roles);
        $this->syncScopes($user, $branchScopes);

        return redirect()->route('users.index')->with('success', 'تم تحديث حساب المستخدم بنجاح.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->can('manage_users') && auth()->user()->can('delete_users'), 403, 'ليس لديك صلاحية إدارة حسابات المستخدمين.');
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }
        if ($user->hasRole('Super Admin') && \Spatie\Permission\Models\Role::findByName('Super Admin')->users()->count() <= 1) {
            return back()->withErrors(['user' => 'لا يمكن حذف آخر مسؤول للنظام.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }

    private function authorizeUserAdministration(): void
    {
        abort_unless(auth()->user()->can('manage_users') && auth()->user()->can('assign_roles'), 403, 'إدارة المستخدمين والأدوار متاحة لمسؤول النظام فقط.');
    }

    private function validateScopesForRoles(array $roles, array $branchIds): void
    {
        if (in_array('Super Admin', $roles, true)) {
            abort_unless(auth()->user()->can('manage_security'), 403, 'لا يمكن إسناد دور مسؤول النظام.');
            return;
        }
        if (array_intersect($roles, ['HR', 'Manager']) && count($branchIds) === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['branch_scopes' => 'حدد فرعًا واحدًا على الأقل لهذا الدور.']);
        }
    }

    private function syncScopes(User $user, array $branchIds): void
    {
        $user->branchScopes()->sync(collect($branchIds)->mapWithKeys(fn ($id) => [(int) $id => ['assigned_by' => auth()->id()]])->all());
    }
}
