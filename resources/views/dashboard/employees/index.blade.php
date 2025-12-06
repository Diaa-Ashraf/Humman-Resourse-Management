@extends('dashboard.master')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>قائمة الموظفين</h3>
        <a href="{{ route('employees.create') }}" class="btn btn-success">إضافة موظف</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الايميل</th>
                <th>القسم</th>
                <th>الوردية</th>
                <th>الوظيفة</th>
                <th>الحالة</th>
                <th width="180">عمليات</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->section->name ?? '-' }}</td>
                    <td>{{ $employee->shift->shift_name ?? '-' }}</td>
                    <td>{{ $employee->position }}</td>
                    <td>{{ $employee->status == 'active' ? 'نشط' : 'غير نشط' }}</td>

                    <td>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-sm">تعديل</a>

                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('متأكد من حذف الموظف؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</div>
@endsection
