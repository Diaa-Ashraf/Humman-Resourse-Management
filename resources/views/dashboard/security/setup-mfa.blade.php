@extends('dashboard.master')
@section('title', 'إعداد المصادقة الثنائية')
@section('content')
<div class="hr-page-heading"><div><h1>إعداد المصادقة الثنائية</h1><p>أضف المفتاح إلى تطبيق المصادقة ثم أكد الرمز.</p></div></div><div class="card" style="max-width:680px"><div class="card-body"><p>أدخل هذا المفتاح يدويًا في تطبيق يدعم رموز TOTP:</p><code class="d-block p-3 bg-light text-break mb-3">{{ $secret }}</code><p class="small text-muted">رابط الإعداد: {{ $uri }}</p>@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif<form method="post" action="{{ route('security.mfa.enable') }}">@csrf<label class="form-label">رمز التطبيق ذو 6 أرقام</label><input class="form-control mb-3" name="code" inputmode="numeric" maxlength="6" required><button class="btn btn-primary">تأكيد وتفعيل</button> <a class="btn btn-light" href="{{ route('security.index') }}">إلغاء</a></form></div></div>
@endsection
