@extends('dashboard.master')
@section('content')
<div class="container mt-4">
    <h2>تسجيل خصم</h2>
    <form method="POST" action="{{ route('deductions.store') }}">
        @csrf

        <div class="mb-3">
            <label>الموظف</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>القيمة</label>
            <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="deduction_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>الحالة</label>
            <select name="status" class="form-control" required>
                <option value="applied" selected>قيد المراجعة</option>
            </select>
        </div>

        <div class="mb-3">
            <label>سبب الخصم</label>
            <input type="text" name="reason" class="form-control" maxlength="255" required>
        </div>

        <button type="submit" class="btn btn-success">إرسال للمراجعة</button>
    </form>
</div>
@endsection
