@extends('dashboard.master')
@section('title', 'طلبات الإجازات')
@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="hr-page-heading mb-4">
        <div>
            <h1>طلبات الإجازات</h1>
            <p>متابعة طلبات الإجازات والاعتمادات وأرصدة الموظفين.</p>
        </div>
        @can('store_leaves')
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                <i class="la la-plus"></i> إضافة طلب إجازة
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3">
            <i class="la la-check-circle font-medium-3 ml-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center mb-3">
            <i class="la la-exclamation-circle font-medium-3 ml-2"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <strong>سجل طلبات الإجازات</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">الموظف</th>
                            <th class="py-3">نوع الإجازة</th>
                            <th class="py-3">من</th>
                            <th class="py-3">إلى</th>
                            <th class="py-3">الحالة</th>
                            <th class="py-3 text-left pl-4">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $leave->employee->name }}</strong>
                                    <small class="d-block text-muted">{{ $leave->employee->branch?->name ?? 'بدون فرع' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $leave->leaveType->name }}</span>
                                </td>
                                <td>{{ $leave->start_date }}</td>
                                <td>{{ $leave->end_date }}</td>
                                <td>
                                    @if($leave->status == 'approved')
                                        <span class="badge badge-success">معتمد</span>
                                    @elseif($leave->status == 'pending')
                                        <span class="badge badge-warning">قيد المراجعة</span>
                                    @else
                                        <span class="badge badge-danger">مرفوض</span>
                                    @endif
                                </td>
                                <td class="text-left pl-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @can('show_leaves')
                                            <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-outline-info btn-sm">عرض</a>
                                        @endcan

                                        @can('review_leaves')
                                            @if($leave->status === 'pending')
                                                <form action="{{ route('leaves.review', $leave) }}" method="POST" class="d-inline">
                                                    @csrf 
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button class="btn btn-success btn-sm">اعتماد</button>
                                                </form>
                                                <form action="{{ route('leaves.review', $leave) }}" method="POST" class="d-inline">
                                                    @csrf 
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-outline-danger btn-sm">رفض</button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('update_leaves')
                                            @if($leave->status === 'pending')
                                                <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-outline-primary btn-sm">تعديل</a>
                                            @endif
                                        @endcan

                                        @can('delete_leaves')
                                            @if($leave->status === 'pending')
                                                <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" type="submit">حذف</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">لا توجد طلبات إجازات مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leaves->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
