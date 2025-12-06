@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">إدارة الزيارات</h2>
        <a href="{{ route('visits.create') }}" class="btn btn-primary">إضافة زيارة</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>مندوب المبيعات</th>
                        <th>Lead</th>
                        <th>Customer</th>
                        <th>التخصص</th>
                        <th>تاريخ الزيارة</th>
                        <th>الوقت</th>
                        <th>المكان</th>
                        <th>الحالة</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visits as $visit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $visit->salesperson?->name }}</td>
                        <td>{{ $visit->lead?->name }}</td>
                        <td>{{ $visit->customer?->name }}</td>
                        <td>{{ $visit->industry?->name }}</td>
                        <td>{{ $visit->visit_date }}</td>
                        <td>{{ $visit->visit_time ?? '-' }}</td>
                        <td>{{ $visit->location ?? '-' }}</td>
                        <td>
                            <span class="badge 
                                {{ $visit->status == 'Successful' ? 'bg-success' : ($visit->status == 'Failed' ? 'bg-danger' : 'bg-info') }}">
                                {{ $visit->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('visits.show', $visit->id) }}" class="btn btn-sm btn-info">عرض</a>
                            <a href="{{ route('visits.edit', $visit->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('visits.destroy', $visit->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('هل تريد حذف هذه الزيارة؟')" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if($visits->isEmpty())
                        <tr>
                            <td colspan="10">لا توجد زيارات مسجلة</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
