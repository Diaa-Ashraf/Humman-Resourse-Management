@extends('dashboard.master')
@section('title','ملف الموظف')
@section('content')
<div class="container-fluid py-3" dir="rtl">
<div class="hr-page-heading"><div><h1>{{ $employee->name }}</h1><p>الرقم الوظيفي: {{ $employee->employee_number ?? 'غير محدد' }} · {{ $employee->position }} · {{ $employee->branch?->name ?? 'بدون فرع' }}</p></div><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('employees.index') }}">قائمة الموظفين</a>@can('update_employees')<a class="btn btn-primary" href="{{ route('employees.edit',$employee) }}">تعديل الملف</a>@endcan</div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row g-3"><div class="col-xl-8"><div class="card"><div class="card-header"><strong>بيانات الموظف</strong></div><div class="card-body"><div class="row g-3">@foreach(['المؤهل'=>$employee->education_level,'القسم'=>$employee->section?->name,'الفرع'=>$employee->branch?->name,'المدير المباشر'=>$employee->manager?->name,'الوردية'=>$employee->shift?->shift_name,'نوع التوظيف'=>$employee->employment_type,'تاريخ التعيين'=>$employee->hire_date?->format('Y-m-d'),'نهاية العقد'=>$employee->contract_end_date?->format('Y-m-d'),'الحالة'=>$employee->status] as $label=>$value)<div class="col-md-4"><small class="text-muted d-block">{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>@endforeach @can('view_sensitive_employee_data')<div class="col-md-4"><small class="text-muted d-block">البريد الإلكتروني</small><strong>{{ $employee->email ?: '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">الهاتف</small><strong>{{ $employee->phone ?: '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">تاريخ الميلاد</small><strong>{{ $employee->date_of_birth?->format('Y-m-d') ?? '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">النوع</small><strong>{{ $employee->gender ?: '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">رقم الهوية</small><strong>{{ $employee->national_id ?: '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">جهة الطوارئ</small><strong>{{ $employee->emergency_contact_name ?: '—' }}</strong></div><div class="col-md-4"><small class="text-muted d-block">هاتف الطوارئ</small><strong>{{ $employee->emergency_contact_phone ?: '—' }}</strong></div><div class="col-12"><small class="text-muted d-block">العنوان</small>{{ $employee->address ?: '—' }}</div>@endcan</div></div></div>
@can('show_employee_documents')<div class="card mt-3"><div class="card-header"><strong>المستندات المرتبطة</strong></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>المستند</th><th>رقم المرجع</th><th>الصلاحية</th><th></th></tr></thead><tbody>@forelse($employee->documents as $document)<tr><td>{{ $document->category }} — {{ $document->title }}</td><td>{{ $document->reference_number ?? '—' }}</td><td>{{ $document->expires_at?->format('Y-m-d') ?? 'غير محدد' }}</td><td>@if($document->file_path)<a href="{{ route('employee-documents.download',$document) }}">تحميل الملف</a>@endif</td></tr>@empty<tr><td colspan="4">لا توجد مستندات مضافة.</td></tr>@endforelse</tbody></table></div></div>@endcan
@can('update_employees')<div class="card mt-3"><div class="card-header"><strong>سجل التغييرات</strong></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>التاريخ</th><th>الحدث</th><th>بواسطة</th><th>التغييرات أو الملاحظات</th></tr></thead><tbody>@forelse($employee->changeLogs as $log)<tr><td>{{ $log->created_at->format('Y-m-d H:i') }}</td><td>{{ ['created'=>'إنشاء الملف','updated'=>'تعديل بيانات','imported'=>'استيراد','terminated'=>'إنهاء خدمة','document_added'=>'إضافة مستند','document_removed'=>'حذف مستند'][$log->event] ?? $log->event }}</td><td>{{ $log->user?->name ?? 'النظام' }}</td><td>{{ $log->notes }} @if($log->changes)<details><summary>عرض التفاصيل</summary><pre class="small mb-0">{{ json_encode($log->changes,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre></details>@endif</td></tr>@empty<tr><td colspan="4">لا توجد تغييرات مسجلة.</td></tr>@endforelse</tbody></table></div></div>@endcan</div>
<div class="col-xl-4">@can('update_employees')<div class="card"><div class="card-header"><strong>مهام دورة الموظف</strong></div><div class="card-body">@forelse($employee->lifecycleTasks->sortBy('completed_at') as $task)<form method="post" action="{{ route('employee-lifecycle-tasks.update',$task) }}" class="d-flex gap-2 align-items-start border-bottom py-2">@csrf @method('patch')<input type="hidden" name="completed" value="{{ $task->completed_at ? 0 : 1 }}"><button class="btn btn-sm {{ $task->completed_at ? 'btn-success' : 'btn-outline-secondary' }}">{{ $task->completed_at ? '✓' : '○' }}</button><div>{{ $task->title }}<small class="d-block text-muted">{{ $task->phase==='onboarding'?'تهيئة وتعيين':'إنهاء خدمة' }} · {{ $task->completed_at?->format('Y-m-d') ?? 'مفتوحة' }}</small></div></form>@empty<p class="text-muted">لا توجد مهام قائمة.</p>@endforelse</div></div>@endcan
@can('update_employees')
    @if($employee->status==='active')
        <div class="card mt-3">
            <div class="card-header"><strong>تسجيل إنهاء الخدمة</strong></div>
            <div class="card-body">
                <form method="post" action="{{ route('employees.terminate',$employee) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">تاريخ آخر يوم عمل</label>
                        <input required type="date" name="termination_date" class="form-control" min="{{ $employee->hire_date?->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب إنهاء الخدمة</label>
                        <textarea required name="termination_reason" maxlength="500" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-outline-danger delete-btn" data-title="إنهاء خدمة الموظف" type="submit">إنهاء الخدمة وإخلاء الطرف</button>
                </form>
            </div>
        </div>
    @elseif($employee->termination_date)
        <div class="card mt-3">
            <div class="card-header"><strong>بيانات إنهاء الخدمة</strong></div>
            <div class="card-body">
                {{ $employee->termination_date->format('Y-m-d') }}
                <p class="mb-0">{{ $employee->termination_reason }}</p>
            </div>
        </div>
    @endif
@endcan
</div></div></div>
@endsection
