@extends('dashboard.master')
@section('title', 'مسيرات الرواتب')
@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="hr-page-heading mb-4">
        <div>
            <h1>مسيرات الرواتب</h1>
            <p>إدارة الرواتب الشهرية وتأكيد الصرف للموظفين.</p>
        </div>
        @can('store_salaries')
            <a href="{{ route('salaries.create') }}" class="btn btn-primary">
                <i class="la la-plus"></i> إضافة راتب
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
            <strong>سجل الرواتب</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">الموظف</th>
                            <th class="py-3">الراتب الأساسي</th>
                            <th class="py-3">صافي الراتب</th>
                            <th class="py-3">تاريخ الصرف</th>
                            <th class="py-3">الحالة</th>
                            <th class="py-3 text-left pl-4">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                            <tr>
                                <td>{{ $salaries->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $salary->employee->name ?? 'موظف محذوف' }}</strong>
                                    <small class="d-block text-muted">{{ $salary->employee->branch?->name ?? 'بدون فرع' }}</small>
                                </td>
                                <td>{{ number_format($salary->amount, 2) }} ج.م</td>
                                <td><strong>{{ number_format($salary->total, 2) }} ج.م</strong></td>
                                <td>{{ $salary->payment_date }}</td>
                                <td>
                                    @if($salary->status == 'paid')
                                        <span class="badge badge-success">تم الصرف</span>
                                    @else
                                        <span class="badge badge-warning">قيد الانتظار</span>
                                    @endif
                                </td>
                                <td class="text-left pl-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @can('pay_salaries')
                                            @if($salary->status === 'pending')
                                                <form action="{{ route('salaries.pay', $salary) }}" method="POST" class="d-inline" onsubmit="return confirm('تأكيد صرف الراتب؟')">
                                                    @csrf 
                                                    @method('PATCH')
                                                    <button class="btn btn-success btn-sm" type="submit">تأكيد الصرف</button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('show_salaries')
                                            <a href="{{ route('salaries.show', $salary->id) }}" class="btn btn-outline-info btn-sm">عرض</a>
                                        @endcan

                                        @can('update_salaries')
                                            @if($salary->status !== 'paid')
                                                <a href="{{ route('salaries.edit', $salary->id) }}" class="btn btn-outline-primary btn-sm">تعديل</a>
                                            @endif
                                        @endcan

                                        @can('delete_salaries')
                                            @if($salary->status !== 'paid')
                                                <form action="{{ route('salaries.destroy', $salary->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
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
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد مسيرات رواتب مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($salaries->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
