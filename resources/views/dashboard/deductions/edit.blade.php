@extends('dashboard.master')
@section('content')
<div class="container mt-4">
    <h2>تعديل الخصم</h2>
    <form method="POST" action="{{ route('deductions.update', $deduction->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>الموظف</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ $deduction->employee_id == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>القيمة</label>
            <input type="number" name="amount" value="{{ $deduction->amount }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="deduction_date" value="{{ $deduction->deduction_date }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>الحالة</label>
            <select name="status" class="form-control" required>
                <option value="applied" {{ $deduction->status == 'applied' ? 'selected' : '' }}>قيد المراجعة</option>
                <option value="rejected" {{ $deduction->status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
            </select>
        </div>

        <div class="mb-3">
            <label>سبب الخصم</label>
            <input type="text" name="reason" value="{{ $deduction->reason }}" class="form-control" maxlength="255" required>
        </div>

        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
    </form>
</div>
@endsection
