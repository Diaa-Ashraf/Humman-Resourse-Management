@extends(auth()->check() ? 'dashboard.master' : 'dashboard.auth.master')

@section('title', 'غير مصرح بالوصول | 403')

@section('content')
<div class="container py-5 text-center" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0 py-5 px-4" style="border-radius: 16px;">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle" style="width: 80px; height: 80px; font-size: 2.2rem;">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                </div>
                <h2 class="fw-bold text-dark mb-2">403 - غير مصرح بالوصول</h2>
                <p class="text-muted fs-6 mb-4">
                    {{ $exception->getMessage() ?: 'ليس لديك الصلاحية الكافية للوصول إلى هذه الصفحة أو تنفيذ هذا الإجراء.' }}
                </p>
                <div>
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-arrow-right me-1"></i> العودة للرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
