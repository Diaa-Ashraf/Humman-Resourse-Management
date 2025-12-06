@extends('dashboard.master')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">إدارة الـ Leads</h2>
        <a href="{{ route('leads.create') }}" class="btn btn-primary">إضافة Lead</a>
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
                        <th>الاسم</th>
                        <th>الموبايل</th>
                        <th>الصناعة</th>
                        <th>مندوب المبيعات</th>
                        <th>الحالة</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $lead->name }}</td>
                        <td>{{ $lead->phone }}</td>
                        <td>{{ $lead->industry?->name }}</td>
                        <td>{{ $lead->salesperson?->name}}</td>
                        <td><span class="badge bg-info">{{ $lead->status }}</span></td>
                        <td>
                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-info">عرض</a>
                            <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('هل تريد حذف هذا الـ Lead ؟')" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                            <a href="{{ route('visits.create', ['lead_id' => $lead->id]) }}" 
   class="btn btn-sm btn-success">
   إضافة زيارة
</a>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
