@extends('dashboard.master')

@section('content')

<div class="container mt-4">
    <h2>تعديل الشيفت</h2>

    <form action="{{ route('shifts.update', $shift->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم الشيفت</label>
            <input type="text" name="shift_name" class="form-control" 
                   value="{{ old('shift_name', $shift->shift_name) }}" required>
            @error('shift_name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>وقت البداية</label>
            <input type="time" name="start_time" class="form-control" 
                   value="{{ old('start_time', $shift->start_time) }}" required>
            @error('start_time') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>وقت النهاية</label>
            <input type="time" name="end_time" class="form-control" 
                   value="{{ old('end_time', $shift->end_time) }}" required>
            @error('end_time') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>الحالة</label>
            <select name="status" class="form-control" required>
                <option value="open" {{ (old('status', $shift->status)=='open') ? 'selected' : '' }}>مفتوح</option>
                <option value="closed" {{ (old('status', $shift->status)=='closed') ? 'selected' : '' }}>مغلق</option>
            </select>
            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button class="btn btn-primary">تحديث</button>
    </form>

</div>
@endsection
