@extends('dashboard.master')
@section('content')
<div class="container mt-4">

    <h3>إضافة موظف جديد</h3>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf

        <div class="row mt-3">

            <div class="col-md-6">
                <label class="form-label">الاسم</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">الايميل</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الهاتف</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الوظيفة</label>
                <input type="text" name="position" class="form-control" value="{{ old('position') }}">
                @error('position') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">القسم</label>
                <select name="section_id" class="form-control">
                    <option value="">اختر القسم</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                @error('section_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الوردية</label>
                <select name="shift_id" class="form-control">
                    <option value="">اختر الوردية</option>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                    @endforeach
                </select>
                @error('shift_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control">
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">تاريخ التعيين</label>
                <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}">
                @error('hire_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-12 mt-3">
                <label class="form-label">الراتب الأساسي</label>
                <input type="number" name="base_salary" class="form-control" min="0" value="{{ old('base_salary') }}">
                @error('base_salary') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

        </div>

        <button class="btn btn-success mt-4">حفظ</button>
    </form>

</div>
@endsection
