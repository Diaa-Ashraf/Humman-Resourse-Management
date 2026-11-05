@php($current = $employee ?? null)
<div class="container-fluid py-3" dir="rtl">
    <div class="hr-page-heading"><div><h1>{{ $current ? 'تعديل ملف موظف' : 'إضافة موظف جديد' }}</h1><p>البيانات الوظيفية والشخصية وبيانات التواصل في ملف موحد.</p></div><a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">العودة للقائمة</a></div>
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <h5 class="alert-heading font-weight-bold mb-2"><i class="la la-exclamation-triangle"></i> يرجى تصحيح الأخطاء التالية:</h5>
            <ul class="mb-0 pr-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ $current ? route('employees.update',$current) : route('employees.store') }}" method="post" class="card">
        @csrf @if($current) @method('put') @endif
        <div class="card-header"><strong>البيانات الأساسية</strong></div><div class="card-body"><div class="row g-3">
            <div class="col-md-6 col-xl-4"><label class="form-label">الاسم الكامل *</label><input class="form-control" name="name" required value="{{ old('name',$current?->name) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">الرقم الوظيفي</label><input class="form-control" name="employee_number" value="{{ old('employee_number',$current?->employee_number) }}" placeholder="يتحدد حسب نظام الشركة"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">رقم الهوية</label><input class="form-control" name="national_id" value="{{ old('national_id',$current?->national_id) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">البريد الإلكتروني *</label><input type="email" class="form-control" name="email" required value="{{ old('email',$current?->email) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">الهاتف *</label><input class="form-control" name="phone" required value="{{ old('phone',$current?->phone) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">تاريخ الميلاد</label><input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth',$current?->date_of_birth?->format('Y-m-d')) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">النوع</label><select class="form-select" name="gender"><option value="">غير محدد</option><option @selected(old('gender',$current?->gender)==='ذكر')>ذكر</option><option @selected(old('gender',$current?->gender)==='أنثى')>أنثى</option></select></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">المؤهل الدراسي</label><input class="form-control" name="education_level" value="{{ old('education_level',$current?->education_level) }}"></div>
            <div class="col-md-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2">{{ old('address',$current?->address) }}</textarea></div>
        </div></div>
        <div class="card-header border-top"><strong>بيانات العمل والهيكل التنظيمي</strong></div><div class="card-body"><div class="row g-3">
            <div class="col-md-6 col-xl-4"><label class="form-label">المسمى الوظيفي *</label><input class="form-control" name="position" required value="{{ old('position',$current?->position) }}"></div>
            <div class="col-md-6 col-xl-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">الفرع</label>
                    @can('store_branches')
                        <a href="{{ route('branches.create') }}" target="_blank" class="small text-primary text-decoration-none">
                            <i class="la la-plus"></i> إضافة فرع
                        </a>
                    @endcan
                </div>
                <select class="form-select @error('branch_id') is-invalid @enderror" id="employee-branch" name="branch_id">
                    <option value="">اختر الفرع</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $current?->branch_id) == $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">القسم *</label>
                    @can('store_sections')
                        <a href="{{ route('sections.create') }}" target="_blank" class="small text-primary text-decoration-none">
                            <i class="la la-plus"></i> إضافة قسم
                        </a>
                    @endcan
                </div>
                <select class="form-select @error('section_id') is-invalid @enderror" id="employee-section" name="section_id" required>
                    <option value="">اختر القسم</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" data-branch-id="{{ $section->branch_id ?? '' }}" @selected(old('section_id', $current?->section_id) == $section->id)>
                            {{ $section->name }}{{ $section->branch ? ' — ' . $section->branch->name : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">تتغير الأقسام حسب الفرع المختار.</small>
                @error('section_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-xl-4">
                <label class="form-label">المدير المباشر</label>
                <select class="form-select" name="manager_employee_id">
                    <option value="">بدون مدير محدد</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_employee_id', $current?->manager_employee_id) == $manager->id)>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">الوردية *</label>
                    @can('store_shifts')
                        <a href="{{ route('shifts.create') }}" target="_blank" class="small text-primary text-decoration-none">
                            <i class="la la-plus"></i> إضافة وردية
                        </a>
                    @endcan
                </div>
                <select class="form-select @error('shift_id') is-invalid @enderror" name="shift_id" required>
                    <option value="">اختر الوردية</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected(old('shift_id', $current?->shift_id) == $shift->id)>
                            {{ $shift->shift_name }}
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-xl-4"><label class="form-label">نوع التوظيف *</label><select class="form-select" name="employment_type" required>@foreach(['دوام كامل','دوام جزئي','موسمي','مؤقت'] as $type)<option value="{{ $type }}" @selected(old('employment_type',$current?->employment_type ?? 'دوام كامل')===$type)>{{ $type }}</option>@endforeach</select></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">الحالة *</label><select class="form-select" name="status"><option value="active" @selected(old('status',$current?->status ?? 'active')==='active')>نشط</option><option value="inactive" @selected(old('status',$current?->status)==='inactive')>غير نشط</option></select></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">تاريخ التعيين *</label><input type="date" class="form-control" name="hire_date" required value="{{ old('hire_date',$current?->hire_date?->format('Y-m-d')) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">نهاية العقد</label><input type="date" class="form-control" name="contract_end_date" value="{{ old('contract_end_date',$current?->contract_end_date?->format('Y-m-d')) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">حساب بوابة الموظف (اختياري)</label><select class="form-select" name="user_id"><option value="">لا يوجد ربط</option>@foreach($users as $portalUser)<option value="{{ $portalUser->id }}" @disabled($portalUser->employee && $portalUser->employee->id!==$current?->id) @selected(old('user_id',$current?->user_id)==$portalUser->id)>{{ $portalUser->name }} — {{ $portalUser->email }}{{ $portalUser->employee && $portalUser->employee->id!==$current?->id ? ' (مرتبط بموظف آخر)' : '' }}</option>@endforeach</select><small class="text-muted">يجب أن يحمل الحساب دور «بوابة الموظف»؛ حد حسابات البرنامج يبقى 10.</small></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">الراتب الأساسي *</label><input type="number" step="0.01" min="0" class="form-control" name="base_salary" required value="{{ old('base_salary',$current?->base_salary) }}"></div>
        </div></div>
        <div class="card-header border-top"><strong>بيانات الرواتب والتأمينات</strong></div><div class="card-body"><div class="row g-3">
            <div class="col-md-6 col-xl-4"><label class="form-label">أجر الاشتراك التأميني (اختياري)</label><input type="number" step="0.01" min="0" class="form-control" name="insurance_wage" value="{{ old('insurance_wage',$current?->insurance_wage) }}"><small class="text-muted">اتركه فارغًا لاستخدام الأساسي ومكونات الأجر التأميني.</small></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">مشمول بالتأمينات</label><select class="form-select" name="insurance_enabled" required><option value="1" @selected((string)old('insurance_enabled',$current?->insurance_enabled ?? true)==='1')>نعم</option><option value="0" @selected((string)old('insurance_enabled',$current?->insurance_enabled ?? true)==='0')>لا</option></select></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">خاضع لضريبة المرتبات</label><select class="form-select" name="tax_enabled" required><option value="1" @selected((string)old('tax_enabled',$current?->tax_enabled ?? true)==='1')>نعم</option><option value="0" @selected((string)old('tax_enabled',$current?->tax_enabled ?? true)==='0')>لا</option></select></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">البنك</label><input class="form-control" name="payroll_bank_name" value="{{ old('payroll_bank_name',$current?->payroll_bank_name) }}"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">رقم الحساب أو الآيبان</label><input class="form-control" name="payroll_bank_account" value="{{ old('payroll_bank_account',$current?->payroll_bank_account) }}"></div>
        </div></div>
        <div class="card-header border-top"><strong>جهة اتصال للطوارئ</strong></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><label class="form-label">اسم جهة الاتصال</label><input class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name',$current?->emergency_contact_name) }}"></div><div class="col-md-6"><label class="form-label">رقم هاتف الطوارئ</label><input class="form-control" name="emergency_contact_phone" value="{{ old('emergency_contact_phone',$current?->emergency_contact_phone) }}"></div></div></div>
        <div class="card-body pt-0"><button class="btn btn-primary">{{ $current ? 'حفظ التعديلات' : 'إنشاء ملف الموظف' }}</button></div>
    </form>
    <script>
        (() => {
            const branch = document.getElementById('employee-branch');
            const section = document.getElementById('employee-section');
            if (!branch || !section) return;

            const filterSections = () => {
                const branchId = branch.value;
                Array.from(section.options).forEach((option, index) => {
                    if (index === 0) return;
                    const sectionBranchId = option.dataset.branchId;
                    const belongsToBranch = !branchId || !sectionBranchId || sectionBranchId === branchId;
                    option.hidden = !belongsToBranch;
                    option.disabled = !belongsToBranch;
                    if (!belongsToBranch && option.selected) section.value = '';
                });
            };

            branch.addEventListener('change', filterSections);
            filterSections();
        })();
    </script>
</div>
