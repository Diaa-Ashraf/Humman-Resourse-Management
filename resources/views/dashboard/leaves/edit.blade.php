@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">تعديل طلب الإجازة</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('leaves.update', $leave->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">الموظف</label>
            <select name="employee_id" class="form-select">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $leave->employee_id == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">نوع الإجازة</label>
            <select name="leave_type_id" class="form-select">
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ $leave->leave_type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">من</label>
            <input type="date" name="start_date" class="form-control" value="{{ $leave->start_date }}">
        </div>

        <div class="mb-3">
            <label class="form-label">إلى</label>
            <input type="date" name="end_date" class="form-control" value="{{ $leave->end_date }}">
        </div>

        <div class="mb-3">
            <label class="form-label">السبب</label>
            <textarea name="reason" class="form-control" rows="4">{{ $leave->reason }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select">
                <option value="pending" {{ $leave->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="approved" {{ $leave->status == 'approved' ? 'selected' : '' }}>معتمد</option>
                <option value="rejected" {{ $leave->status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
            </select>
        </div>

        <button class="btn btn-primary">تحديث</button>
        <a href="{{ route('leaves.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
