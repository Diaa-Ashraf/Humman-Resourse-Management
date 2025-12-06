@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-4">عرض Lead</h3>

    <div class="card">
        <div class="card-body">

            <p><strong>الاسم:</strong> {{ $lead->name }}</p>
            <p><strong>الهاتف:</strong> {{ $lead->phone }}</p>
            <p><strong>الإيميل:</strong> {{ $lead->email }}</p>
            <p><strong>العنوان:</strong> {{ $lead->address }}</p>
            <p><strong>القطاع:</strong> {{ $lead->industry?->name }}</p>
            <p><strong>مندوب المبيعات:</strong> {{ $lead->salesperson?->name }}</p>
            <p><strong>المصدر:</strong> {{ $lead->source }}</p>
            <p><strong>الحالة:</strong> <span class="badge bg-info">{{ $lead->status }}</span></p>

            @if($lead->customer_id)
                <p class="text-success mt-3">✨ هذا Lead تم تحويله إلى Customer رقم ({{ $lead->customer_id }})</p>
            @endif

        </div>
    </div>

    <a href="{{ route('leads.index') }}" class="btn btn-secondary mt-4">رجوع</a>

</div>
@endsection
