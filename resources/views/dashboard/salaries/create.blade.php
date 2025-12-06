@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">إضافة راتب جديد</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('salaries.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">الموظف</label>
            <select name="employee_id" class="form-select" required>
                <option value="">اختيار موظف</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }} - {{ $emp->base_salary }} جنيه</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الدفع</label>
            <input type="date" name="payment_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select">
                <option value="pending">قيد الانتظار</option>
                <option value="paid">مدفوع</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">حفظ</button>
        <a href="{{ route('salaries.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
