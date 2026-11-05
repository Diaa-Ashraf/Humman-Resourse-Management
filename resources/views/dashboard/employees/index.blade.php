@extends('dashboard.master')
@section('title','ملفات الموظفين')
@section('content')
<div class="container-fluid py-3" dir="rtl">
<div class="hr-page-heading">
    <div>
        <h1>ملفات الموظفين</h1>
        <p>قاعدة بيانات موحدة للموظفين والهيكل التنظيمي ودورة العمل.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('export_employees')
            <a class="btn btn-outline-primary" href="{{ route('employees.export') }}">تصدير CSV</a>
        @endcan
        @can('store_employees')
            @if($employeeCount < 1000)
                <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="la la-user-plus"></i> إضافة موظف</a>
            @endif
        @endcan
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('import_result'))<div class="alert alert-info">تم استيراد {{ session('import_result.created') }} موظف. أخطاء الصفوف: {{ session('import_result.error_count') }} @if(session('import_result.errors'))<details><summary>تفاصيل الأخطاء</summary><ul class="mb-0">@foreach(session('import_result.errors') as $error)<li>{{ $error }}</li>@endforeach</ul></details>@endif</div>@endif
@can('import_employees')<div class="card mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3"><div><strong>استيراد الموظفين من Excel</strong><small class="d-block text-muted">احفظ الملف بصيغة CSV UTF-8. استخدم القالب لمعرفة الأعمدة المطلوبة وأكواد الفروع.</small><a href="{{ route('employees.import-template') }}" class="small">تحميل قالب الاستيراد</a></div><form method="post" enctype="multipart/form-data" action="{{ route('employees.import') }}" class="d-flex gap-2">@csrf<input class="form-control" type="file" name="file" accept=".csv,.txt" required><button class="btn btn-outline-primary text-nowrap">استيراد CSV</button></form></div></div>@endcan
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2"><div class="col-lg-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الرقم الوظيفي أو البريد أو الهاتف"></div><div class="col-lg-2"><select class="form-select" name="branch_id"><option value="">كل الفروع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div><div class="col-lg-2"><select class="form-select" name="section_id"><option value="">كل الأقسام</option>@foreach($sections as $section)<option value="{{ $section->id }}" @selected(request('section_id')==$section->id)>{{ $section->name }}</option>@endforeach</select></div><div class="col-lg-2"><select class="form-select" name="status"><option value="">كل الحالات</option><option value="active" @selected(request('status')==='active')>نشط</option><option value="inactive" @selected(request('status')==='inactive')>غير نشط</option></select></div><div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-fill">بحث</button><a class="btn btn-outline-secondary" href="{{ route('employees.index') }}">مسح</a></div></div></div></form>
<div class="card"><div class="card-header d-flex justify-content-between"><strong>سجل الموظفين</strong><span>{{ $employeeCount }} / 1,000 موظف</span></div><div class="card-body"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>#</th><th>الرقم الوظيفي والاسم</th><th>القسم والفرع</th><th>المدير</th><th>الوردية</th><th>المسمى</th><th>الحالة</th><th>الإجراءات</th></tr></thead><tbody>
@forelse($employees as $employee)<tr><td>{{ $employees->firstItem()+$loop->index }}</td><td><a href="{{ route('employees.show',$employee) }}"><strong>{{ $employee->name }}</strong></a><small class="d-block text-muted">{{ $employee->employee_number ?? '—' }} · {{ $employee->email }}</small></td><td>{{ $employee->section?->name ?? '—' }}<small class="d-block text-muted">{{ $employee->branch?->name ?? 'بدون فرع' }}</small></td><td>{{ $employee->manager?->name ?? '—' }}</td><td>{{ $employee->shift?->shift_name ?? '—' }}</td><td>{{ $employee->position }}</td><td><span class="badge {{ $employee->status==='active'?'bg-success':'bg-secondary' }}">{{ $employee->termination_date?'منتهي الخدمة':($employee->status==='active'?'نشط':'غير نشط') }}</span></td><td class="text-nowrap">@can('show_employees')<a class="btn btn-sm btn-outline-secondary" href="{{ route('employees.show',$employee) }}">الملف</a>@endcan @can('update_employees')<a class="btn btn-sm btn-outline-primary" href="{{ route('employees.edit',$employee) }}">تعديل</a>@endcan</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">لا توجد سجلات مطابقة.</td></tr>@endforelse
</tbody></table></div>{{ $employees->links() }}</div></div></div>
@endsection
