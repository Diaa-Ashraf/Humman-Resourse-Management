@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">إضافة طلب إجازة</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('leaves.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">الموظف</label>
            <select name="employee_id" class="form-select" required>
                <option value="">اختر موظف</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">نوع الإجازة</label>
            <select name="leave_type_id" class="form-select" required>
                <option value="">اختر نوع الإجازة</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">من</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">إلى</label>
            <input type="date" name="end_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">السبب</label>
            <textarea name="reason" class="form-control" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select" required>
                <option value="pending">قيد الانتظار</option>
                <option value="approved">معتمد</option>
                <option value="rejected">مرفوض</option>
            </select>
        </div>

        <button class="btn btn-success">حفظ</button>
        <a href="{{ route('leaves.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
