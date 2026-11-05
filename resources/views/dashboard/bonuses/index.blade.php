@extends('dashboard.master')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>المكافآت والبدلات</h1>
        <p>سجل المكافآت المقدمة للموظفين واعتماداتها.</p>
    </div>
    @can('store_bonuses')
        <a href="{{ route('bonuses.create') }}" class="btn btn-primary">تسجيل مكافأة</a>
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
                    @forelse($bonuses as $bonus)
                        <tr>
                            <td>{{ $bonuses->firstItem() + $loop->index }}</td>
                            <td>{{ $bonus->employee->name ?? 'موظف محذوف' }}</td>
                            <td>{{ number_format($bonus->amount, 2) }} جنيه</td>
                            <td>{{ $bonus->bonus_date }}</td>
                            <td>
                                <span class="badge {{ $bonus->status === 'approved' ? 'badge-success' : ($bonus->status === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ['applied' => 'قيد المراجعة', 'approved' => 'معتمدة', 'rejected' => 'مرفوضة'][$bonus->status] ?? $bonus->status }}
                                </span>
                            </td>
                            <td>
                                @can('review_bonuses')
                                    @if($bonus->status === 'applied')
                                        <form action="{{ route('bonuses.review', $bonus) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">اعتماد</button>
                                        </form>
                                        <form action="{{ route('bonuses.review', $bonus) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">رفض</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('update_bonuses')
                                    @if($bonus->status === 'applied')
                                        <a href="{{ route('bonuses.edit', $bonus->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    @endif
                                @endcan

                                @can('delete_bonuses')
                                    @if($bonus->status === 'applied')
                                        <form action="{{ route('bonuses.destroy', $bonus->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" data-title="حذف طلب المكافأة">حذف</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد مكافآت مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($bonuses->hasPages())
        <div class="card-footer">
            {{ $bonuses->links() }}
        </div>
    @endif
</div>
@endsection
