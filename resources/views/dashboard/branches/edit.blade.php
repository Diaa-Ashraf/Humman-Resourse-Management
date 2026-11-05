@extends('dashboard.master')
@section('title', 'تعديل الفرع')
@section('content')
<div class="hr-page-heading"><div><h1>تعديل الفرع</h1><p>تحديث بيانات {{ $branch->name }}.</p></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('branches.update', $branch) }}">@csrf @method('PUT') @include('dashboard.branches.partials.form')<button class="btn btn-primary mt-3">حفظ التعديلات</button> <a class="btn btn-light mt-3" href="{{ route('branches.index') }}">إلغاء</a></form></div></div>
@endsection
