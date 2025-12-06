@extends('dashboard.master')
@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>تفاصيل الموظف</h3>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">رجوع</a>
    </div>

    <div class="card shadow">

        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ $employee->name }}</h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>الاسم:</strong>
                    <p>{{ $employee->name }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الايميل:</strong>
                    <p>{{ $employee->email }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الهاتف:</strong>
                    <p>{{ $employee->phone }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الوظيفة:</strong>
                    <p>{{ $employee->position }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>القسم:</strong>
                    <p>{{ $employee->section->name ?? '-' }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الوردية:</strong>
                    <p>{{ $employee->shift->name ?? '-' }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الحالة:</strong>
                    <p class="{{ $employee->status == 'active' ? 'text-success' : 'text-danger' }}">
                        {{ $employee->status == 'active' ? 'نشط' : 'غير نشط' }}
                    </p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>تاريخ التعيين:</strong>
                    <p>{{ $employee->hire_date }}</p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>الراتب الأساسي:</strong>
                    <p>{{ number_format($employee->base_salary, 2) }} جنيه</p>
                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">

            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary me-2">تعديل</a>

            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                  onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">حذف</button>
            </form>

        </div>

    </div>

</div>
@endsection
