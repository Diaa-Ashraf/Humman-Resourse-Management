@extends('dashboard.master')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>الورديات</h1>
        <p>مواعيد وساعات العمل وتوزيع الموظفين على الفترات.</p>
    </div>
    @can('store_shifts')
        <a href="{{ route('shifts.create') }}" class="btn btn-primary">إضافة وردية</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>وقت البداية</th>
                        <th>وقت النهاية</th>
                        <th>الموظفون</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>{{ $shift->shift_name }}</td>
                            <td>{{ $shift->start_time }}</td>
                            <td>{{ $shift->end_time }}</td>
                            <td>{{ number_format($shift->employees_count) }}</td>
                            <td>
                                <span class="badge {{ $shift->status == 'open' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $shift->status == 'open' ? 'مفتوح' : 'مغلق' }}
                                </span>
                            </td>
                            <td>
                                @can('update_shifts')
                                    <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-sm btn-primary">تعديل</a>
                                @endcan

                                @can('delete_shifts')
                                    @if($shift->employees_count === 0 && $shift->status === 'closed')
                                        <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" data-title="حذف هذه الوردية">حذف</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد ورديات مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
