@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">تعديل الراتب</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('salaries.update', $salary->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">الموظف</label>
            <select name="employee_id" class="form-select" required>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $salary->employee_id == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }} - {{ $emp->base_salary }} جنيه
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الدفع</label>
            <input type="date" name="payment_date" class="form-control" value="{{ $salary->payment_date }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select">
                <option value="pending" {{ $salary->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="paid" {{ $salary->status == 'paid' ? 'selected' : '' }}>مدفوع</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('salaries.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
