@extends('dashboard.master')
@section('content')
<div class="container py-5" style="max-width:520px"><div class="card"><div class="card-body p-4"><h1 class="h4 mb-2">التحقق بخطوتين</h1><p class="text-muted">أدخل الرمز المكوّن من 6 أرقام من تطبيق المصادقة.</p>@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif<form method="post" action="{{ route('mfa.challenge.verify') }}">@csrf<label class="form-label">رمز التحقق</label><input class="form-control mb-3" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus><button class="btn btn-primary w-100">تحقق ودخول</button></form></div></div></div>
@endsection
