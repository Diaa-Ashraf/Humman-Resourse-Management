@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">تفاصيل الزيارة</h2>
        <a href="{{ route('visits.index') }}" class="btn btn-secondary">رجوع للزيارات</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>مندوب المبيعات</th>
                    <td>{{ $visit->salesperson?->name }}</td>
                </tr>
                <tr>
                    <th>Lead</th>
                    <td>{{ $visit->lead?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Customer</th>
                    <td>{{ $visit->customer?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>التخصص</th>
                    <td>{{ $visit->industry?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الزيارة</th>
                    <td>{{ $visit->visit_date->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>وقت الزيارة</th>
                    <td>{{ $visit->visit_date->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>المكان</th>
                    <td>{{ $visit->location ?? '-' }}</td>
                </tr>
                <tr>
                    <th>ملاحظات</th>
                    <td>{{ $visit->notes ?? '-' }}</td>
                </tr>
                <tr>
                    <th>الحالة</th>
                    <td>{{ $visit->status }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>{{ $visit->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <th>آخر تحديث</th>
                    <td>{{ $visit->updated_at->format('Y-m-d H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
