@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">تعديل الزيارة</h2>
        <a href="{{ route('visits.index') }}" class="btn btn-secondary">رجوع للزيارات</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('visits.update', $visit->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- مندوب المبيعات -->
                <div class="mb-3">
                    <label class="form-label">مندوب المبيعات</label>
                    <select name="sales_id" class="form-select" required>
                        <option value="">اختر مندوب المبيعات</option>
                        @foreach($sales as $employee)
                            <option value="{{ $employee->id }}" {{ $visit->sales_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lead -->
                <div class="mb-3">
                    <label class="form-label">Lead (اختياري)</label>
                    <select name="lead_id" class="form-select">
                        <option value="">اختر Lead</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" {{ $visit->lead_id == $lead->id ? 'selected' : '' }}>
                                {{ $lead->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer -->
                <div class="mb-3">
                    <label class="form-label">Customer (اختياري)</label>
                    <select name="customer_id" class="form-select">
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $visit->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Industry -->
                <div class="mb-3">
                    <label class="form-label">التخصص</label>
                    <input type="text" class="form-control" name="industry_id" value="{{ $visit->industry?->name ?? '' }}" readonly>
                </div>

                <!-- تاريخ الزيارة -->
                <div class="mb-3">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="date" class="form-control" name="visit_date" value="{{ $visit->visit_date->format('Y-m-d') }}" required>
                </div>

                <!-- وقت الزيارة -->
                <div class="mb-3">
                    <label class="form-label">وقت الزيارة</label>
                    <input type="time" class="form-control" name="visit_time" value="{{ $visit->visit_date->format('H:i') }}">
                </div>

                <!-- المكان -->
                <div class="mb-3">
                    <label class="form-label">المكان</label>
                    <input type="text" class="form-control" name="location" value="{{ $visit->location }}">
                </div>

                <!-- الملاحظات -->
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control">{{ $visit->notes }}</textarea>
                </div>

                <!-- الحالة -->
                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select" required>
                        <option value="Pending" {{ $visit->status=='Pending' ? 'selected' : '' }}>معلق</option>
                        <option value="Successful" {{ $visit->status=='Successful' ? 'selected' : '' }}>ناجحة</option>
                        <option value="Failed" {{ $visit->status=='Failed' ? 'selected' : '' }}>فاشلة</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">تحديث</button>
            </form>
        </div>
    </div>
</div>
@endsection
