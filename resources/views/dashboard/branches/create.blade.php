@extends('dashboard.master')
@section('title', 'إضافة فرع')
@section('content')
<div class="hr-page-heading"><div><h1>إضافة فرع</h1><p>أدخل بيانات موقع العمل الجديد.</p></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('branches.store') }}">@csrf @include('dashboard.branches.partials.form')<button class="btn btn-primary mt-3">حفظ الفرع</button> <a class="btn btn-light mt-3" href="{{ route('branches.index') }}">إلغاء</a></form></div></div>
@endsection
