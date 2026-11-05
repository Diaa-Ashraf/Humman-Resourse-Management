@extends('dashboard.master')
@section('title', 'مستندات الموظفين')
@section('content')
<div class="container-fluid py-2" dir="rtl">
    <div class="hr-page-heading mb-4">
        <div>
            <h1>مستندات الموظفين والتنبيهات</h1>
            <p>سجل العقود والهويات والشهادات والتراخيص مع متابعة تواريخ الانتهاء وتنبيهات الصلاحية.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="la la-check-circle font-medium-3 ml-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif 
    
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="la la-exclamation-circle font-medium-3 ml-2"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    @can('store_employee_documents')
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="la la-file-upload text-primary font-medium-3 ml-2"></i>
            <strong>تسجيل مستند جديد</strong>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" action="{{ route('employee-documents.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label class="form-label">الموظف <span class="text-danger">*</span></label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->branch?->name ?? 'بدون فرع' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">تصنيف المستند <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option>عقد عمل</option>
                            <option>بطاقة هوية</option>
                            <option>شهادة دراسية</option>
                            <option>رخصة قيادة / مهنية</option>
                            <option>تقرير طبي</option>
                            <option>مستند آخر</option>
                        </select>
                    </div>

                    <div class="col-lg-5 col-md-12 mb-3">
                        <label class="form-label">عنوان / اسم المستند <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="مثال: عقد عمل سنوي 2026" required>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">رقم المرجع / الرقم القومي</label>
                        <input type="text" class="form-control" name="reference_number" placeholder="رقم المستند إن وجد">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">تاريخ الإصدار</label>
                        <input type="date" class="form-control" name="issued_at">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">تاريخ الانتهاء</label>
                        <input type="date" class="form-control" name="expires_at">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">الملف المرفق (PDF أو صورة)</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control" name="file">
                    </div>
                </div>

                <div class="mt-3 pt-2 text-left">
                    <button type="submit" class="btn btn-primary">
                        <i class="la la-save ml-1"></i> حفظ بيانات المستند
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="la la-folder-open text-primary font-medium-3 ml-2"></i>
                <strong>سجل المستندات</strong>
            </div>

            <form method="get" class="d-flex flex-wrap align-items-center gap-2 m-0">
                <select class="form-select form-select-sm" name="employee_id" style="min-width: 170px;">
                    <option value="">جميع الموظفين</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id')==$employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>

                <select class="form-select form-select-sm" name="expiry" style="min-width: 160px;">
                    <option value="">كل حالات الصلاحية</option>
                    <option value="expired" @selected(request('expiry')==='expired')>منتهي الصلاحية</option>
                    <option value="soon" @selected(request('expiry')==='soon')>ينتهي خلال 60 يومًا</option>
                </select>

                <button type="submit" class="btn btn-sm btn-outline-primary" style="padding: 6px 14px;">
                    <i class="la la-filter ml-1"></i> تصفية
                </button>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 pr-4">الموظف</th>
                            <th class="py-3">نوع وعنوان المستند</th>
                            <th class="py-3">رقم المرجع</th>
                            <th class="py-3">تاريخ الإصدار</th>
                            <th class="py-3">تاريخ الانتهاء</th>
                            <th class="py-3">حالة الصلاحية</th>
                            <th class="py-3 text-left pl-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            @php($expired = $document->expires_at && $document->expires_at->isPast())
                            @php($soon = $document->expires_at && !$expired && $document->expires_at->lte(today()->addDays(60)))
                            <tr>
                                <td class="pr-4">
                                    <strong>{{ $document->employee->name }}</strong>
                                    <small class="d-block text-muted">{{ $document->employee->branch?->name ?? 'بدون فرع' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary ml-1">{{ $document->category }}</span>
                                    <span>{{ $document->title }}</span>
                                </td>
                                <td>{{ $document->reference_number ?: '—' }}</td>
                                <td>{{ $document->issued_at?->format('Y-m-d') ?: '—' }}</td>
                                <td>{{ $document->expires_at?->format('Y-m-d') ?: 'ساري غير محدد' }}</td>
                                <td>
                                    @if($expired)
                                        <span class="badge badge-danger">منتهي الصلاحية</span>
                                    @elseif($soon)
                                        <span class="badge badge-warning">قارب على الانتهاء</span>
                                    @else
                                        <span class="badge badge-success">ساري ومفعل</span>
                                    @endif
                                </td>
                                <td class="text-left pl-4">
                                    <div class="d-inline-flex gap-1">
                                        @if($document->file_path)
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('employee-documents.download', $document) }}" title="تحميل المستند">
                                                <i class="la la-download"></i>
                                            </a>
                                        @endif 
                                        @can('delete_employee_documents')
                                            <form method="post" action="{{ route('employee-documents.destroy', $document) }}" class="d-inline" onsubmit="return confirm('هل تريد حذف سجل هذا المستند؟')">
                                                @csrf 
                                                @method('delete')
                                                <button class="btn btn-sm btn-outline-danger" type="submit" title="حذف">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="la la-inbox font-large-1 d-block mb-2"></i>
                                    لا توجد مستندات مسجلة حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($documents->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
