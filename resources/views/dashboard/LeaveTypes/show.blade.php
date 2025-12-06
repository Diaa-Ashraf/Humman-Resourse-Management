@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <h3 class="fw-bold mb-4">عرض نوع الإجازة</h3>

    <div class="card">
        <div class="card-body">

            <p><strong>اسم النوع:</strong> {{ $leaveType->name }}</p>
            <hr>

            <p><strong>الوصف:</strong> {{ $leaveType->description ?? '-' }}</p>
            <hr>

            <p><strong>الحد الأقصى للأيام:</strong> {{ $leaveType->max_days ?? '-' }}</p>
            <hr>

            <p><strong>الحالة:</strong>
                <span class="badge bg-{{ $leaveType->status === 'active' ? 'success' : 'secondary' }}">
                    {{ $leaveType->status === 'active' ? 'نشط' : 'غير نشط' }}
                </span>
            </p>

            <a href="{{ route('leave-types.index') }}" class="btn btn-secondary mt-3">رجوع</a>

        </div>
    </div>

</div>
@endsection
