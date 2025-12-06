@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">المرتبات</h2>
        <a href="{{ route('salaries.create') }}" class="btn btn-primary">إضافة راتب</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>الموظف</th>
                <th>الراتب الأساسي</th>
                <th>الإجمالي</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>العمليات</th>
            </tr>
        </thead>

        <tbody>
            @foreach($salaries as $salary)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $salary->employee->name }}</td>
                <td>{{ $salary->amount }}</td>
                <td>{{ $salary->total }}</td>
                <td>{{ $salary->payment_date }}</td>
                <td>
                    @if($salary->status == 'paid')
                        <span class="badge bg-success">مدفوع</span>
                    @else
                        <span class="badge bg-warning">قيد الانتظار</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('salaries.show', $salary->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('salaries.edit', $salary->id) }}" class="btn btn-primary btn-sm">تعديل</a>

                    <form action="{{ route('salaries.destroy', $salary->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('هل انت متأكد من الحذف؟')">حذف</button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
 