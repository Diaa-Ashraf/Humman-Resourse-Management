@extends('dashboard.master')
@section('title', 'السلف والقروض')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="hr-page-heading">
        <div>
            <h1>السلف والقروض</h1>
            <p>طلبات بسجل اعتماد وجدول أقساط يخصم عند صرف مسير الرواتب.</p>
        </div>
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

    @can('store_advances')
        <div class="card mb-4">
            <div class="card-header">
                <strong>تسجيل طلب سلفة</strong>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('advances.store') }}" class="row g-3">
                    @csrf
                    <div class="col-lg-4">
                        <label class="form-label">الموظف</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">اختر الموظف</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }} — {{ $employee->branch?->name ?? 'بدون فرع' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label">قيمة السلفة</label>
                        <input class="form-control" name="amount" type="number" min="1" step="0.01" required>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label">عدد الأقساط</label>
                        <input class="form-control" name="installments" type="number" min="1" max="36" required>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label class="form-label">أول استقطاع</label>
                        <input class="form-control" name="first_deduction_date" type="date" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">السبب</label>
                        <input class="form-control" name="reason" maxlength="500" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">إرسال للاعتماد</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <strong>طلبات السلف وجدول السداد</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>الموظف</th>
                        <th>الطلب</th>
                        <th>القيمة / الرصيد</th>
                        <th>قسط تقريبي</th>
                        <th>أول استقطاع</th>
                        <th>الحالة</th>
                        <th>الأقساط</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $advance)
                        <tr>
                            <td>
                                <strong>{{ $advance->employee->name ?? 'موظف محذوف' }}</strong>
                                <small class="d-block text-muted">{{ $advance->employee->branch?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $advance->reason }}</td>
                            <td>{{ number_format($advance->amount, 2) }} / {{ number_format($advance->balance, 2) }} ج.م</td>
                            <td>{{ number_format($advance->installment_amount, 2) }} × {{ $advance->installments }}</td>
                            <td>{{ $advance->first_deduction_date?->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge {{ $advance->status === 'approved' ? 'badge-success' : ($advance->status === 'rejected' ? 'badge-danger' : ($advance->status === 'repaid' ? 'badge-info' : 'badge-warning')) }}">
                                    {{ ['pending' => 'بانتظار الاعتماد', 'approved' => 'معتمدة', 'rejected' => 'مرفوضة', 'repaid' => 'مسددة'][$advance->status] ?? $advance->status }}
                                </span>
                            </td>
                            <td>
                                <details>
                                    <summary class="cursor-pointer text-primary">
                                        {{ $advance->installmentRecords ? $advance->installmentRecords->count() : 0 }} قسط
                                    </summary>
                                    <div class="p-2 border rounded mt-1 bg-light small">
                                        @forelse($advance->installmentRecords ?? [] as $installment)
                                            <div class="py-1 border-bottom">
                                                {{ $installment->due_date?->format('Y-m-d') }} · 
                                                <strong>{{ number_format($installment->amount, 2) }} ج.م</strong> · 
                                                <span class="badge {{ $installment->status === 'paid' ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $installment->status === 'paid' ? 'مسدد' : 'مجدول' }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-muted">لا توجد أقساط مسجلة بعد.</div>
                                        @endforelse
                                    </div>
                                </details>
                            </td>
                            <td>
                                @if($advance->status === 'pending' && auth()->user()->can('review_advances'))
                                    <div class="d-flex gap-1">
                                        <form method="post" action="{{ route('advances.review', $advance) }}" class="d-inline">
                                            @csrf
                                            @method('patch')
                                            <button name="decision" value="approved" type="submit" class="btn btn-sm btn-success">اعتماد</button>
                                        </form>
                                        <form method="post" action="{{ route('advances.review', $advance) }}" class="d-inline">
                                            @csrf
                                            @method('patch')
                                            <button name="decision" value="rejected" type="submit" class="btn btn-sm btn-outline-danger">رفض</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">لا توجد طلبات سلف.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($advances->hasPages())
            <div class="card-footer">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
