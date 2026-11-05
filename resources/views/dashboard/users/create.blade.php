@extends('dashboard.master')

@section('content')
<div class="hr-page-heading"><div><h1>إضافة مستخدم جديد</h1><p>أنشئ حسابًا وحدد له مستوى الوصول المناسب.</p></div></div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card"><div class="card-body"><form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">كلمة المرور</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="12" required>
            <small class="text-muted">12 خانة على الأقل، تشمل حرفًا كبيرًا وصغيرًا ورقمًا ورمزًا.</small>
        </div>

        <div class="mb-3"><label class="form-label">الفروع المسموح بها</label><div class="row">@foreach($branches as $branch)<label class="col-md-4 col-6 mb-2"><input type="checkbox" name="branch_scopes[]" value="{{ $branch->id }}" @checked(in_array($branch->id, old('branch_scopes', [])))> {{ $branch->name }}</label>@endforeach</div><small class="text-muted">المستخدم يرى بيانات الموظفين في الفروع المختارة فقط. مسؤول النظام لديه نطاق شامل.</small></div>

        <div class="mb-3">
            <label class="form-label">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
        </div>

        @can('assign_roles')
        <div class="mb-3">
            <label class="form-label">الدور</label>
            <select name="roles[]" class="form-control" multiple required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(in_array($role->name, old('roles', [])))>{{ $role->display_name ?: $role->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">اختيار دور واحد على الأقل مطلوب لتحديد صلاحيات الحساب</small>
        </div>

        <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
        @endcan
        <a href="{{ route('users.index') }}" class="btn btn-light">إلغاء</a>
    </form></div></div>
@endsection
