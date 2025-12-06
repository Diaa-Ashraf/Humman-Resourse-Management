@extends('dashboard.master')
@section('content')
<div class="container mt-4">
    <h2>Edit Deduction</h2>
    <form method="POST" action="{{ route('deductions.update', $deduction->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Employee</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ $deduction->employee_id == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Amount</label>
            <input type="number" name="amount" value="{{ $deduction->amount }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="deduction_date" value="{{ $deduction->deduction_date }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="applied" {{ $deduction->status == 'applied' ? 'selected' : '' }}>Applied</option>
                <option value="approved" {{ $deduction->status == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $deduction->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Reason</label>
            <input type="text" name="reason" value="{{ $deduction->reason }}" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
