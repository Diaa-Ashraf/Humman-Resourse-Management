@extends('dashboard.master')

@section('title', 'بوابة الخدمة الذاتية')

@section('content')
<div class="container-fluid py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow-sm border-0 text-center py-5 px-4" style="border-radius: 16px;">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 80px; height: 80px; font-size: 2.2rem;">
                        <i class="bi bi-person-exclamation"></i>
                    </span>
                </div>

                <h3 class="fw-bold mb-3 text-dark">بوابة الخدمة الذاتية للموظف</h3>

                @if(!$employee)
                    <p class="text-muted fs-6 mb-4 leading-relaxed">
                        حسابك الحالي غير مرتبط بأي ملف موظف داخل النظام.
                        <br>
                        بوابة الخدمة الذاتية مخصصة للموظفين لتقديم طلبات الإجازات، والخطابات، ومراجعة التقييمات.
                    </p>
                @else
                    <p class="text-muted fs-6 mb-4 leading-relaxed">
                        ملف الموظف المرتبط بحسابك الحالي (<strong>{{ $employee->name }}</strong>) غير نشط حالياً (حالة الحساب: <span class="badge bg-secondary">غير نشط</span>).
                        <br>
                        بوابة الخدمة الذاتية متاحة فقط للموظفين في حالة العمل النشطة.
                    </p>
                @endif

                <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-right me-1"></i> العودة للوحة المتابعة
                    </a>

                    @can('update_employees')
                        @if($employee)
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-pencil-square me-1"></i> تعديل وتفعيل ملف الموظف
                            </a>
                        @else
                            <a href="{{ route('employees.create') }}" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-person-plus me-1"></i> إنشاء أو ربط ملف موظف
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
