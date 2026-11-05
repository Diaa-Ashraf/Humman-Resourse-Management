@extends('dashboard.master')
@section('title', 'تعديل المستخدم')

@section('content')
<div class="hr-page-heading">
    <div>
        <h1>تعديل حساب المستخدم</h1>
        <p>حدّث بيانات الدخول والدور الوظيفي ونطاق الفروع للمستخدم.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">كلمة المرور الجديدة <small class="text-muted">(اختياري، 12 خانة مع حروف وأرقام ورمز)</small></label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="12">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">الأدوار والصلاحيات</label>
                    <select name="roles[]" class="form-control" multiple required>
                        @foreach($allRoles as $role)
                            <option value="{{ $role->name }}" {{ old('roles') ? (in_array($role->name, old('roles', [])) ? 'selected' : '') : ($user->hasRole($role->name) ? 'selected' : '') }}>
                                {{ $role->display_name ?: $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">اختر دورًا واحدًا على الأقل. لا تسند دور مسؤول النظام إلا للحسابات المعتمدة.</small>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">نطاق الفروع</label>
                    <div class="row border rounded p-3 bg-light mx-0">
                        @forelse($branches as $branch)
                            <div class="col-md-4 col-6 mb-2">
                                <label class="d-flex align-items-center gap-2 cursor-pointer mb-0">
                                    <input type="checkbox" name="branch_scopes[]" value="{{ $branch->id }}" @checked(in_array((string)$branch->id, array_map('strval', (array)old('branch_scopes', $selectedScopes))))>
                                    <span>{{ $branch->name }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="col-12 text-muted">لا توجد فروع مسجلة.</div>
                        @endforelse
                    </div>
                    <small class="text-muted d-block mt-1">الأدوار التشغيلية لا ترى إلا بيانات الفروع المحددة هنا.</small>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">حفظ التعديلات</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">إلغاء</a>
        </form>
    </div>
</div>
@endsection
