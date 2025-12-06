@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">إدارة أنواع الإجازات</h3>
        <a href="{{ route('leave-types.create') }}" class="btn btn-primary">إضافة نوع جديد</a>
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
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->description ?? '-' }}</td>
                            <td>{{ $type->max_days ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $type->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $type->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('leave-types.show', $type->id) }}" class="btn btn-info btn-sm">عرض</a>
                                <a href="{{ route('leave-types.edit', $type->id) }}" class="btn btn-warning btn-sm">تعديل</a>

                                <form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟')">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">لا توجد بيانات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
