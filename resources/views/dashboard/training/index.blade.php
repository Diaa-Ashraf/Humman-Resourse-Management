@extends('dashboard.master')
@section('title', 'التدريب والتطوير')
@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="hr-page-heading mb-4">
        <div>
            <h1>التدريب والتطوير المهني</h1>
            <p>تحليل الاحتياجات والخطة السنوية والميزانية والحضور وقياس الأثر.</p>
        </div>
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

    @can('store_training')
    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <strong>خطة تدريب سنوية</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('training.plans.store') }}" class="d-grid gap-2">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <label class="form-label">السنة</label>
                                <input type="number" name="year" min="2020" max="2100" value="{{ now()->year }}" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">الميزانية</label>
                                <input type="number" name="budget" min="0" step="0.01" value="0" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">اسم الخطة</label>
                            <input name="name" class="form-control" placeholder="اسم الخطة" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">الفرع</label>
                            <select name="branch_id" class="form-select">
                                <option value="">على مستوى الشركة</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات الخطة"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ كمسودة</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <strong>تحليل احتياجات التدريب</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('training.needs.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12 mb-2">
                            <label class="form-label">الخطة المستهدفة</label>
                            <select name="training_plan_id" class="form-select" required>
                                <option value="">اختر مسودة خطة</option>
                                @foreach($plans->where('status','draft') as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->year }} · {{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">المهارة أو الاحتياج</label>
                            <input name="skill_or_need" class="form-control" placeholder="المهارة أو الاحتياج" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select">
                                <option value="normal">عادي</option>
                                <option value="high">مرتفع</option>
                                <option value="critical">حرج</option>
                                <option value="low">منخفض</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">القسم</label>
                            <select name="section_id" class="form-select">
                                <option value="">احتياج موظف فردي أو عام</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">قسم: {{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">الموظف (اختياري)</label>
                            <select name="employee_id" class="form-select">
                                <option value="">اختر موظفًا اختياريًا</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">مبرر الاحتياج</label>
                            <textarea name="rationale" class="form-control" rows="2" placeholder="مبرر الاحتياج"></textarea>
                        </div>
                        <div class="col-12 text-left">
                            <button type="submit" class="btn btn-outline-primary" {{ $plans->where('status','draft')->isEmpty() ? 'disabled' : '' }}>
                                إضافة الاحتياج
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="card mb-4">
        <div class="card-header">
            <strong>خطط التدريب واعتماداتها</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 pr-3">الخطة</th>
                        <th class="py-3">الميزانية</th>
                        <th class="py-3">الاحتياجات</th>
                        <th class="py-3">تكلفة البرامج</th>
                        <th class="py-3">الحالة</th>
                        <th class="py-3 text-left pl-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td class="pr-3">
                                <strong>{{ $plan->year }} · {{ $plan->name }}</strong>
                                <small class="d-block text-muted">{{ $plan->branch?->name ?? 'كل الفروع' }}</small>
                                <details class="mt-1">
                                    <summary class="text-primary small" style="cursor: pointer;">عرض الاحتياجات ({{ $plan->needs->count() }})</summary>
                                    <div class="p-2 border rounded bg-light mt-1">
                                        @foreach($plan->needs as $need)
                                            <div class="small border-bottom py-1">
                                                {{ $need->skill_or_need }} · <span class="badge badge-info">{{ $need->priority }}</span> · {{ $need->employee?->name ?? $need->section?->name ?? 'عام' }}
                                            </div>
                                        @endforeach
                                        @if($plan->status === 'draft' && auth()->user()->can('store_training'))
                                            <form method="post" action="{{ route('training.plans.needs.store', $plan) }}" class="row g-1 mt-2">
                                                @csrf
                                                <input name="skill_or_need" class="form-control form-control-sm col" placeholder="احتياج جديد" required>
                                                <select name="priority" class="form-select form-select-sm col">
                                                    <option value="normal">عادي</option>
                                                    <option value="high">مرتفع</option>
                                                    <option value="critical">حرج</option>
                                                </select>
                                                <select name="section_id" class="form-select form-select-sm col" required>
                                                    <option value="">اختر القسم</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="rationale" class="form-control form-control-sm col-12" placeholder="المبرر"></textarea>
                                                <button class="btn btn-sm btn-outline-primary mt-1">إضافة</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            </td>
                            <td>{{ number_format($plan->budget, 2) }} ج.م</td>
                            <td>{{ $plan->needs_count }}</td>
                            <td>{{ number_format($plan->courses_sum_cost ?? 0, 2) }} ج.م</td>
                            <td>
                                <span class="badge {{ $plan->status === 'approved' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ['draft'=>'مسودة','submitted'=>'بانتظار الاعتماد','approved'=>'معتمد','returned'=>'معاد للتعديل'][$plan->status] ?? $plan->status }}
                                </span>
                            </td>
                            <td class="text-left pl-3">
                                @if($plan->status === 'draft' && auth()->user()->can('store_training'))
                                    <details class="d-inline-block mb-1">
                                        <summary class="btn btn-sm btn-outline-secondary">تعديل</summary>
                                        <div class="p-2 border rounded bg-white mt-1 text-right" style="min-width: 260px;">
                                            <form method="post" action="{{ route('training.plans.update', $plan) }}" class="d-grid gap-1">
                                                @csrf 
                                                @method('put')
                                                <input name="name" value="{{ $plan->name }}" class="form-control form-control-sm mb-1" placeholder="اسم الخطة" required>
                                                <input name="budget" value="{{ $plan->budget }}" type="number" min="0" step="0.01" class="form-control form-control-sm mb-1" placeholder="الميزانية" required>
                                                <select name="branch_id" class="form-select form-select-sm mb-1">
                                                    <option value="">كل الفروع</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}" @selected($plan->branch_id == $branch->id)>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="notes" class="form-control form-control-sm mb-1" placeholder="ملاحظات">{{ $plan->notes }}</textarea>
                                                <button class="btn btn-sm btn-primary">حفظ</button>
                                            </form>
                                        </div>
                                    </details>
                                    <form method="post" action="{{ route('training.plans.submit', $plan) }}" class="d-inline">
                                        @csrf 
                                        @method('patch')
                                        <button class="btn btn-sm btn-outline-primary">إرسال للاعتماد</button>
                                    </form>
                                @elseif($plan->status === 'submitted' && (auth()->user()->can('approve_training_plans') || auth()->user()->hasRole('Super Admin')))
                                    <form method="post" action="{{ route('training.plans.decision', $plan) }}" class="d-inline-flex gap-1">
                                        @csrf 
                                        @method('patch')
                                        <input name="notes" class="form-control form-control-sm" placeholder="ملاحظة" style="max-width: 130px;">
                                        <button class="btn btn-sm btn-success" name="decision" value="approved">اعتماد</button>
                                        <button class="btn btn-sm btn-outline-warning" name="decision" value="returned">إرجاع</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">لا توجد خطط سنوية مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
            <div class="card-footer bg-white">
                {{ $plans->links() }}
            </div>
        @endif
    </div>

    @can('store_training')
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <strong>إنشاء برنامج تدريبي</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('training.courses.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">اسم البرنامج</label>
                                <input name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">الجهة المقدمة</label>
                                <input name="provider" class="form-control">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">الخطة المعتمدة</label>
                                <select name="training_plan_id" class="form-select">
                                    <option value="">بدون خطة</option>
                                    @foreach($activePlans->where('status','approved') as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->year }} · {{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">الفرع</label>
                                <select name="branch_id" class="form-select">
                                    <option value="">كل الفروع</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">الساعات</label>
                                <input type="number" name="duration_hours" min="1" value="1" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">التكلفة</label>
                                <input type="number" step="0.01" min="0" name="cost" value="0" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">من</label>
                                <input type="date" name="starts_at" class="form-control">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">إلى</label>
                                <input type="date" name="ends_at" class="form-control">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="planned">مخطط</option>
                                    <option value="active">جارٍ</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label">وصف البرنامج</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">حفظ البرنامج</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <strong>تسجيل موظف في برنامج</strong>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('training.enrollments.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">البرنامج التدريبي</label>
                            <select class="form-select" name="training_course_id" required>
                                <option value="">-- اختر البرنامج --</option>
                                @foreach($activeCourses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الموظف</label>
                            <select class="form-select" name="employee_id" required>
                                <option value="">-- اختر الموظف --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} — {{ $employee->branch?->name ?? 'بدون فرع' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">تسجيل الموظف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="card mb-4">
        <div class="card-header">
            <strong>البرامج التدريبية</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 pr-3">البرنامج</th>
                        <th class="py-3">الجهة</th>
                        <th class="py-3">الخطة</th>
                        <th class="py-3">الفترة</th>
                        <th class="py-3">المدة</th>
                        <th class="py-3">التكلفة</th>
                        <th class="py-3">المشاركون</th>
                        <th class="py-3 text-left pl-3">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td class="pr-3"><strong>{{ $course->title }}</strong></td>
                            <td>{{ $course->provider ?? '—' }}</td>
                            <td>{{ $course->plan?->name ?? '—' }}</td>
                            <td>{{ $course->starts_at?->format('Y-m-d') ?? '—' }} إلى {{ $course->ends_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $course->duration_hours }} ساعة</td>
                            <td>{{ number_format($course->cost, 2) }} ج.م</td>
                            <td>{{ $course->enrollments_count }}</td>
                            <td class="text-left pl-3">
                                <span class="badge badge-info">
                                    {{ ['planned'=>'مخطط','active'=>'جارٍ','completed'=>'مكتمل','cancelled'=>'ملغي'][$course->status] ?? $course->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">لا توجد برامج تدريبية.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
            <div class="card-footer bg-white">
                {{ $courses->links() }}
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <strong>الحضور ونتائج التعلم وقياس الأثر</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 pr-3">الموظف</th>
                        <th class="py-3">البرنامج</th>
                        <th class="py-3">الحالة</th>
                        <th class="py-3">حضور (ساعة)</th>
                        <th class="py-3">قبل/بعد</th>
                        <th class="py-3">الأثر</th>
                        <th class="py-3">الإنجاز</th>
                        <th class="py-3 text-left pl-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        <tr>
                            <td class="pr-3"><strong>{{ $enrollment->employee->name }}</strong></td>
                            <td>{{ $enrollment->course->title }}</td>
                            @can('update_training')
                                @php($formId = 'enrollment-' . $enrollment->id)
                                <td>
                                    <select form="{{ $formId }}" name="status" class="form-select form-select-sm">
                                        @foreach(['enrolled'=>'مسجل','completed'=>'مكتمل','failed'=>'لم يجتز','withdrawn'=>'منسحب'] as $key=>$label)
                                            <option value="{{ $key }}" @selected($enrollment->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input form="{{ $formId }}" name="attended_hours" type="number" min="0" max="{{ $enrollment->course->duration_hours }}" step="0.25" value="{{ $enrollment->attended_hours }}" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <input form="{{ $formId }}" name="assessment_before" type="number" min="0" max="100" step="0.01" value="{{ $enrollment->assessment_before }}" class="form-control form-control-sm" placeholder="قبل">
                                        <input form="{{ $formId }}" name="assessment_after" type="number" min="0" max="100" step="0.01" value="{{ $enrollment->assessment_after }}" class="form-control form-control-sm" placeholder="بعد">
                                    </div>
                                </td>
                                <td>
                                    <select form="{{ $formId }}" name="effectiveness_rating" class="form-select form-select-sm">
                                        <option value="">—</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected($enrollment->effectiveness_rating == $i)>{{ $i }}/5</option>
                                        @endfor
                                    </select>
                                    <input form="{{ $formId }}" name="feedback" value="{{ $enrollment->feedback }}" class="form-control form-control-sm mt-1" placeholder="ملاحظات الأثر">
                                </td>
                                <td>
                                    <input form="{{ $formId }}" type="date" name="completed_at" value="{{ $enrollment->completed_at?->format('Y-m-d') }}" class="form-control form-control-sm">
                                </td>
                                <td class="text-left pl-3">
                                    <form id="{{ $formId }}" method="post" action="{{ route('training.enrollments.update', $enrollment) }}">
                                        @csrf 
                                        @method('patch')
                                    </form>
                                    <input form="{{ $formId }}" name="score" type="number" min="0" max="100" step="0.01" value="{{ $enrollment->score }}" class="form-control form-control-sm mb-1" placeholder="نتيجة">
                                    <button form="{{ $formId }}" class="btn btn-sm btn-outline-primary">حفظ</button>
                                </td>
                            @else
                                <td>{{ $enrollment->status }}</td>
                                <td>{{ $enrollment->attended_hours }} / {{ $enrollment->course->duration_hours }}</td>
                                <td>{{ $enrollment->assessment_before ?? '—' }} / {{ $enrollment->assessment_after ?? '—' }}</td>
                                <td>{{ $enrollment->effectiveness_rating ?? '—' }}/5</td>
                                <td>{{ $enrollment->completed_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-left pl-3">{{ $enrollment->score ?? '—' }} / 100</td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">لا توجد تسجيلات حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($enrollments->hasPages())
            <div class="card-footer bg-white">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
