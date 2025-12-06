@extends('dashboard.master')

@section('content')
<div class="container mt-4">
    <h2>إضافة قسم جديد</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sections.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">اسم القسم</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('sections.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
