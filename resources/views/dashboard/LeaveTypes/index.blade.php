@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">إدارة أنواع الإجازات</h3>
        @can('store_leave_types')<a href="{{ route('leave-types.create') }}" class="btn btn-primary">إضافة نوع جديد</a>@endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم النوع</th>
                        <th>الوصف</th>
                        <th>الحد الأقصى للأيام</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td>{{ $types->firstItem() + $loop->index }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->description ?? '-' }}</td>
                            <td>{{ $type->max_days ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $type->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $type->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td>
                                @can('show_leave_types')<a href="{{ route('leave-types.show', $type->id) }}" class="btn btn-info btn-sm">عرض</a>@endcan
                                @can('update_leave_types')<a href="{{ route('leave-types.edit', $type->id) }}" class="btn btn-warning btn-sm">تعديل</a>@endcan

                                @can('delete_leave_types')
                                @if($type->leaves_count === 0 && $type->status !== 'active')<form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟')">
                                        حذف
                                    </button>
                                </form>@endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">لا توجد بيانات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $types->links() }}
        </div>
    </div>

</div>
@endsection
