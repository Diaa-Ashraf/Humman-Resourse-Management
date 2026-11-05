@extends('dashboard.master')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>الأقسام</h1>
        <p>تنظيم فرق العمل وإسناد الموظفين للأقسام.</p>
    </div>
    @can('store_sections')
        <a href="{{ route('sections.create') }}" class="btn btn-primary">إضافة قسم</a>
    @endcan
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم القسم</th>
                        <th>الكود</th>
                        <th>الفرع</th>
                        <th>الحالة</th>
                        <th>عدد الموظفين</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sections as $section)
                        <tr>
                            <td>{{ $sections->firstItem() + $loop->index }}</td>
                            <td>{{ $section->name }}</td>
                            <td>{{ $section->code ?? '—' }}</td>
                            <td>{{ $section->branch?->name ?? 'على مستوى الشركة' }}</td>
                            <td>
                                <span class="badge {{ $section->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $section->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td>{{ number_format($section->employees_count) }}</td>
                            <td>
                                @can('update_sections')
                                    <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-sm btn-primary">تعديل</a>
                                @endcan

                                @can('delete_sections')
                                    @if($section->employees_count === 0)
                                        <form action="{{ route('sections.destroy', $section->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" data-title="حذف هذا القسم">حذف</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">لا توجد أقسام مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sections->hasPages())
        <div class="card-footer">
            {{ $sections->links() }}
        </div>
    @endif
</div>
@endsection
