@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <h3 class="fw-bold mb-4">إضافة نوع إجازة جديد</h3>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('leave-types.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">اسم النوع</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">الحد الأقصى للأيام</label>
                    <input type="number" name="max_days" class="form-control" value="{{ old('max_days') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                <button class="btn btn-primary">حفظ</button>
                <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">رجوع</a>

            </form>

        </div>
    </div>

</div>
@endsection
