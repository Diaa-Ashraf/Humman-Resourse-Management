@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-4">إضافة Lead جديد</h3>

    <form action="{{ route('leads.store') }}" method="POST">
        @csrf

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">اسم العميل</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">رقم الموبايل</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">الإيميل</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">العنوان</label>
                <input type="text" name="address" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">القطاع</label>
                <select name="industry_id" class="form-control">
                    <option value="">اختر القطاع</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry->id }}">{{ $industry->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">مندوب المبيعات</label>
                <select name="sales_id" class="form-control" required>
                    <option value="">اختر المندوب</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">مصدر الـ Lead</label>
                <select name="source" class="form-control" required>
                    <option value="Facebook">Facebook</option>
                    <option value="Website">Website</option>
                    <option value="Phone">Phone</option>
                    <option value="Visit">Visit</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">حالة الـ Lead</label>
                <select name="status" class="form-control" required>
                    <option value="New">New</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Hot">Hot</option>
                    <option value="Converted">Converted</option>
                    <option value="Lost">Lost</option>
                </select>
            </div>

        </div>

        <button class="btn btn-success mt-3">حفظ</button>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary mt-3">رجوع</a>

    </form>
</div>
@endsection
