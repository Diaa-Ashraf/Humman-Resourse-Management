@extends('dashboard.master')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>الخصومات</h1>
        <p>مراجعة الخصومات المقترحة واعتماد أثرها على راتب الموظف.</p>
    </div>
    @can('store_deductions')
        <a href="{{ route('deductions.create') }}" class="btn btn-primary">تسجيل خصم</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الموظف</th>
                        <th>القيمة</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $deduction)
                        <tr>
                            <td>{{ $deductions->firstItem() + $loop->index }}</td>
                            <td>{{ $deduction->employee->name ?? 'موظف محذوف' }}</td>
                            <td>{{ number_format($deduction->amount, 2) }} جنيه</td>
                            <td>{{ $deduction->deduction_date }}</td>
                            <td>
                                <span class="badge {{ $deduction->status === 'approved' ? 'badge-success' : ($deduction->status === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ['applied' => 'قيد المراجعة', 'approved' => 'معتمد', 'rejected' => 'مرفوض'][$deduction->status] ?? $deduction->status }}
                                </span>
                            </td>
                            <td>
                                @can('review_deductions')
                                    @if($deduction->status === 'applied')
                                        <form action="{{ route('deductions.review', $deduction) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">اعتماد</button>
                                        </form>
                                        <form action="{{ route('deductions.review', $deduction) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">رفض</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('update_deductions')
                                    @if($deduction->status === 'applied')
                                        <a href="{{ route('deductions.edit', $deduction->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    @endif
                                @endcan

                                @can('delete_deductions')
                                    @if($deduction->status === 'applied')
                                        <form action="{{ route('deductions.destroy', $deduction->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" data-title="حذف طلب الخصم">حذف</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد خصومات مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($deductions->hasPages())
        <div class="card-footer">
            {{ $deductions->links() }}
        </div>
    @endif
</div>
@endsection
