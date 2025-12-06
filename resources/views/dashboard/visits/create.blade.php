@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">إضافة زيارة جديدة</h2>
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
            <form action="{{ route('visits.store') }}" method="POST">
                @csrf

                {{-- Sales --}}
                @if($user->hasRole('Sales'))
                    <input type="hidden" name="sales_id" value="{{ $salesperson->id }}">
                @else
                    <div class="mb-3">
                        <label class="form-label">مندوب المبيعات</label>
                        <select name="sales_id" class="form-select" required>
                            <option value="">اختر مندوب المبيعات</option>
                            @foreach($allSales as $employee)
                                <option value="{{ $employee->id }}" {{ old('sales_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Lead --}}
                <div class="mb-3">
                    <label class="form-label">Lead (اختياري)</label>
                    <select name="lead_id" class="form-select">
                        <option value="">اختر Lead</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" {{ (old('lead_id') ?? ($selectedLead?->id ?? '')) == $lead->id ? 'selected' : '' }}>
                                {{ $lead->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Customer --}}
                <div class="mb-3">
                    <label class="form-label">Customer (اختياري)</label>
                    <select name="customer_id" class="form-select">
                        <option value="">اختر العميل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Industry --}}
                <div class="mb-3">
                    <label class="form-label">التخصص</label>
                    <input type="text" class="form-control" name="industry_id" 
                        value="{{ old('industry_id') ?? ($selectedLead?->industry?->name ?? '') }}" readonly>
                </div>

                {{-- Visit Date --}}
                <div class="mb-3">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="date" class="form-control" name="visit_date" value="{{ old('visit_date') }}" required>
                </div>

                {{-- Visit Time --}}
                <div class="mb-3">
                    <label class="form-label">وقت الزيارة</label>
                    <input type="time" class="form-control" name="visit_time" value="{{ old('visit_time') }}">
                </div>

                {{-- Location --}}
                <div class="mb-3">
                    <label class="form-label">المكان</label>
                    <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select" required>
                        <option value="Pending" {{ old('status')=='Pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="Successful" {{ old('status')=='Successful' ? 'selected' : '' }}>ناجحة</option>
                        <option value="Failed" {{ old('status')=='Failed' ? 'selected' : '' }}>فاشلة</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">حفظ</button>

            </form>
        </div>
    </div>
</div>
@endsection
