@extends('dashboard.master')
@section('title', 'الفروع')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>الفروع</h1>
        <p>إدارة مواقع العمل وربط الموظفين بكل فرع.</p>
    </div>
    @if(auth()->user()->can('store_branches') && $branchCount < 20)
        <a class="btn btn-primary" href="{{ route('branches.create') }}">
            <i class="la la-plus"></i> إضافة فرع
        </a>
    @endif
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>الفرع</th>
                        <th>كود الفرع</th>
                        <th>الموظفون</th>
                        <th>الحالة</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>
                                <strong>{{ $branch->name }}</strong>
                                <small class="d-block text-muted">{{ $branch->address ?: 'لا يوجد عنوان' }}</small>
                            </td>
                            <td>{{ $branch->code }}</td>
                            <td>{{ number_format($branch->employees_count) }} موظف</td>
                            <td>
                                <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $branch->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td class="text-left">
                                @can('update_branches')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('branches.edit', $branch) }}">تعديل</a>
                                @endcan

                                @can('delete_branches')
                                    @if(!$branch->is_active && $branch->employees_count === 0)
                                        <form class="d-inline" method="POST" action="{{ route('branches.destroy', $branch) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger delete-btn" data-title="حذف هذا الفرع" type="submit">حذف</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">لا توجد فروع بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($branches->hasPages())
        <div class="card-footer">
            {{ $branches->links() }}
        </div>
    @endif
</div>
@endsection
