@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">تفاصيل طلب الإجازة</h2>

    <div class="card p-4">

        <p><strong>الموظف:</strong> {{ $leave->employee->name }}</p>

        <p><strong>نوع الإجازة:</strong> {{ $leave->employee->name }}</p>

        <p><strong>من:</strong> {{ $leave->start_date }}</p>
        <p><strong>إلى:</strong> {{ $leave->end_date }}</p>

        <p><strong>السبب:</strong> {{ $leave->reason ?? '—' }}</p>

        <p><strong>الحالة:</strong>
            @if($leave->status == 'approved')
                <span class="badge bg-success">معتمد</span>
            @elseif($leave->status == 'pending')
                <span class="badge bg-warning">قيد الانتظار</span>
            @else
                <span class="badge bg-danger">مرفوض</span>
            @endif
        </p>

        <a href="{{ route('leaves.index') }}" class="btn btn-secondary mt-3">رجوع</a>

    </div>

</div>
@endsection
