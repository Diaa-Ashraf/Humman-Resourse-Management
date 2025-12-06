@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">عرض تفاصيل الراتب</h2>

    <div class="card p-4">

        <h4>الموظف: {{ $salary->employee->name }}</h4>

        <p><strong>الراتب الأساسي:</strong> {{ $salary->amount }}</p>
        <p><strong>الإجمالي:</strong> {{ $salary->total }}</p>
        <p><strong>تاريخ الدفع:</strong> {{ $salary->payment_date }}</p>

        <p><strong>الحالة:</strong>
            @if($salary->status == 'paid')
                <span class="badge bg-success">مدفوع</span>
            @else
                <span class="badge bg-warning">قيد الانتظار</span>
            @endif
        </p>

        <a href="{{ route('salaries.index') }}" class="btn btn-secondary mt-3">رجوع</a>

    </div>

</div>
@endsection
