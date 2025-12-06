@extends('dashboard.master')
@section('content')
<div class="container mt-4">

    <h3>تعديل بيانات موظف</h3>

    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mt-3">

            <div class="col-md-6">
                <label class="form-label">الاسم</label>
                <input type="text" name="name" class="form-control" value="{{ $employee->name }}">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">الايميل</label>
                <input type="email" name="email" class="form-control"
                       value="{{ $employee->email }}">
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الهاتف</label>
                <input type="text" name="phone" class="form-control" value="{{ $employee->phone }}">
                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الوظيفة</label>
                <input type="text" name="position" class="form-control" value="{{ $employee->position }}">
                @error('position') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">القسم</label>
                <select name="section_id" class="form-control">
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" {{ $employee->section_id == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                @error('section_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الوردية</label>
                <select name="shift_id" class="form-control">
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ $employee->shift_id == $shift->id ? 'selected' : '' }}>
                            {{ $shift->shift_name }}
                        </option>
                    @endforeach
                </select>
                @error('shift_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control">
                    <option value="active"   {{ $employee->status == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mt-3">
                <label class="form-label">تاريخ التعيين</label>
                <input type="date" name="hire_date" class="form-control" value="{{ $employee->hire_date }}">
                @error('hire_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-12 mt-3">
                <label class="form-label">الراتب الأساسي</label>
                <input type="number" name="base_salary" class="form-control" min="0" value="{{ $employee->base_salary }}">
                @error('base_salary') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

        </div>

        <button class="btn btn-primary mt-4">تحديث</button>
    </form>

</div>
@endsection
