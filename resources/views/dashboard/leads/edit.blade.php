@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-4">تعديل Lead</h3>

    <form action="{{ route('leads.update', $lead->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">اسم العميل</label>
                <input type="text" name="name" class="form-control" value="{{ $lead->name }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">رقم الموبايل</label>
                <input type="text" name="phone" class="form-control" value="{{ $lead->phone }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">الإيميل</label>
                <input type="email" name="email" class="form-control" value="{{ $lead->email }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">العنوان</label>
                <input type="text" name="address" class="form-control" value="{{ $lead->address }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">القطاع</label>
                <select name="industry_id" class="form-control">
                    @foreach($industries as $industry)
                        <option value="{{ $industry->id }}" {{ $lead->industry_id == $industry->id ? 'selected' : '' }}>
                            {{ $industry->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">مندوب المبيعات</label>
                <select name="sales_id" class="form-control" required>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $lead->sales_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">مصدر الـ Lead</label>
                <select name="source" class="form-control" required>
                    @foreach(['Facebook','Website','Phone','Visit','Other'] as $src)
                        <option value="{{ $src }}" {{ $lead->source == $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control" required>
                    @foreach(['New','In Progress','Hot','Converted','Lost'] as $st)
                        <option value="{{ $st }}" {{ $lead->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <button class="btn btn-success mt-3">تحديث</button>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary mt-3">رجوع</a>

    </form>
</div>
@endsection
