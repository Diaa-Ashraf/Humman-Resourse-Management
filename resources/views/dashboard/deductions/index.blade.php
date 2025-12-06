@extends('dashboard.master')
@section('content')
<div class="container mt-4">
    <h2>Deductions Management</h2>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <a href="{{ route('deductions.create') }}" class="btn btn-primary mb-3">Add Deduction</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deductions as $deduction)
            <tr>
                <td>{{ $deduction->id }}</td>
                <td>{{ $deduction->employee->name }}</td>
                <td>{{ $deduction->amount }}</td>
                <td>{{ $deduction->deduction_date }}</td>
                <td>{{ ucfirst($deduction->status) }}</td>
                <td>
                    <a href="{{ route('deductions.edit', $deduction->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('deductions.destroy', $deduction->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $deductions->links() }}
</div>
@endsection
