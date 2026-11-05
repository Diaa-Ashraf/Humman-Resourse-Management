@extends('dashboard.master')
@section('content')
<div class="container mt-4">
    <h2>تعديل المكافأة</h2>
    <form method="POST" action="{{ route('bonuses.update', $bonus->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>الموظف</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ $bonus->employee_id == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>القيمة</label>
            <input type="number" name="amount" value="{{ $bonus->amount }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="bonus_date" value="{{ $bonus->bonus_date }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>الحالة</label>
            <select name="status" class="form-control" required>
                <option value="applied" {{ $bonus->status == 'applied' ? 'selected' : '' }}>قيد المراجعة</option>
                <option value="rejected" {{ $bonus->status == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
            </select>
        </div>

        <div class="mb-3">
            <label>سبب المكافأة</label>
            <input type="text" name="reason" value="{{ $bonus->reason }}" class="form-control" maxlength="255" required>
        </div>

        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
    </form>
</div>
@endsection
