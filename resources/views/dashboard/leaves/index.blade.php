@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between mb-4">
        <h2 class="fw-bold">طلبات الإجازات</h2>
        <a href="{{ route('leaves.create') }}" class="btn btn-primary">إضافة طلب إجازة</a>
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
                <th>نوع الإجازة</th>
                <th>من</th>
                <th>إلى</th>
                <th>الحالة</th>
                <th>العمليات</th>
            </tr>
        </thead>

        <tbody>
            @foreach($leaves as $leave)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $leave->employee->name }}</td>
                <td>{{ $leave->leaveType->name }}</td>
                <td>{{ $leave->start_date }}</td>
                <td>{{ $leave->end_date }}</td>

                <td>
                    @if($leave->status == 'approved')
                        <span class="badge bg-success">معتمد</span>
                    @elseif($leave->status == 'pending')
                        <span class="badge bg-warning">قيد الانتظار</span>
                    @else
                        <span class="badge bg-danger">مرفوض</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-primary btn-sm">تعديل</a>

                    <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('هل انت متأكد من الحذف؟')">
                            حذف
                        </button>
                    </form>
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
