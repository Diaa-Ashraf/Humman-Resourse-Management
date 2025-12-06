@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <h3 class="fw-bold mb-4">تعديل نوع الإجازة</h3>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('leave-types.update', $leaveType->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">اسم النوع</label>
                    <input type="text" name="name" class="form-control" value="{{ $leaveType->name }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control">{{ $leaveType->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">الحد الأقصى للأيام</label>
                    <input type="number" name="max_days" class="form-control" value="{{ $leaveType->max_days }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ $leaveType->status == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ $leaveType->status == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <button class="btn btn-warning">تحديث</button>
                <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">رجوع</a>

            </form>

        </div>
    </div>

</div>
@endsection
