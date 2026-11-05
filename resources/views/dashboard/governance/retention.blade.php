@extends('dashboard.master')
@section('title', 'الاحتفاظ والخصوصية')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>الاحتفاظ والخصوصية</h1>
        <p>حدد مدة الاحتفاظ والأساس النظامي لكل فئة بيانات. التنظيف التلقائي معطل حتى يفعّله مسؤول مخول.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4">
    @forelse($policies as $policy)
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <strong class="fs-5 text-dark">
                        <i class="la la-shield-alt text-primary me-1"></i> {{ $policy->label }}
                    </strong>
                    <span class="badge {{ $policy->cleanup_enabled ? 'badge-success' : 'badge-secondary' }}">
                        {{ $policy->cleanup_enabled ? 'التنظيف التلقائي مفعّل' : 'التنظيف معطّل' }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('retention.update', $policy) }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label class="form-label fw-bold">مدة الاحتفاظ (بالأيام)</label>
                            <div class="input-group">
                                <input class="form-control" type="number" min="1" max="36500" name="retention_days" value="{{ old('retention_days', $policy->retention_days) }}" required>
                                <span class="input-group-text">يوم</span>
                            </div>
                            <small class="text-muted">مثال: 365 يوم (سنة واحدة)، 1825 يوم (5 سنوات).</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الأساس القانوني أو سياسة الشركة</label>
                            <textarea class="form-control" name="legal_basis" rows="3" required placeholder="اذكر المادة القانونية أو اللائحة الداخلية الملزمة بالحفظ">{{ old('legal_basis', $policy->legal_basis) }}</textarea>
                        </div>

                        @if(in_array($policy->data_category, ['audit_logs', 'user_sessions']))
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="d-flex align-items-center gap-2 cursor-pointer mb-0">
                                    <input type="checkbox" name="cleanup_enabled" value="1" @checked($policy->cleanup_enabled)>
                                    <span class="fw-bold">تفعيل الحذف التلقائي للسجلات الأقدم من هذه المدة</span>
                                </label>
                            </div>
                        @else
                            <div class="mb-3 p-3 bg-light rounded border text-muted small">
                                <i class="la la-info-circle text-info"></i> تظل الملفات وسجلات تغييرات الموظفين محفوظة كأرشيف دائم ولا يحذفها التنظيف التلقائي.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <button class="btn btn-primary" type="submit">
                                <i class="la la-save"></i> حفظ السياسة
                            </button>
                            <small class="text-muted">
                                آخر تحديث: {{ $policy->updated_at?->format('Y-m-d H:i') ?? 'لم تُحدّث' }}
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-5 text-center text-muted">
                <i class="la la-folder-open la-3x mb-2"></i>
                <p>لا توجد فئات احتفاظ مضافة في قاعدة البيانات.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
