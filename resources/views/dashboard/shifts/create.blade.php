@extends('dashboard.master')

@section('content')
<div class="container mt-4">
    <h2>Create Shift</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('shifts.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Shift Name</label>
            <input type="text" name="shift_name" class="form-control" value="{{ old('shift_name') }}" required>
        </div>

        <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
        </div>

        <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>

        <button class="btn btn-success">Create Shift</button>
    </form>
</div>
@endsection
