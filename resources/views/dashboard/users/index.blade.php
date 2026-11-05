@extends('dashboard.master')
@section('title', 'المستخدمون والصلاحيات')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>المستخدمون والصلاحيات</h1>
        <p>إدارة حسابات فريق العمل والتحكم في مستوى الوصول.</p>
    </div>
    @if(auth()->user()->can('store_users') && auth()->user()->can('assign_roles') && $users->total() < 10)
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="la la-user-plus"></i> إضافة مستخدم
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong>حد النظام:</strong> الخطة الحالية مهيأة لما يصل إلى 10 مستخدمين. استخدم الأدوار ونطاقات الفروع لتحديد البيانات والعمليات المتاحة لكل حساب.
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الأدوار</th>
                        <th>نطاق الفروع</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge badge-info ml-1">{{ $role->display_name ?: $role->name }}</span>
                                @empty
                                    <span class="badge badge-warning">بلا دور</span>
                                @endforelse
                            </td>
                            <td>
                                @if($user->hasRole('Super Admin'))
                                    <span class="badge badge-success">جميع الفروع</span>
                                @elseif($user->branchScopes->isNotEmpty())
                                    {{ $user->branchScopes->pluck('name')->join('، ') }}
                                @else
                                    {{ $user->employee?->branch?->name ?? 'لا يوجد نطاق مخصص' }}
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->can('manage_users') && auth()->user()->can('assign_roles'))
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">تعديل</a>
                                @endif

                                @canany(['manage_users','delete_users'])
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm delete-btn" data-title="حذف هذا الحساب" type="submit">حذف</button>
                                        </form>
                                    @endif
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد حسابات مستخدمين.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
