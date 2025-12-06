@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h3 class="mb-3">إضافة تخصص جديد</h3>

    <form action="{{ route('industry.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">اسم التخصص</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button class="btn btn-primary">حفظ</button>
        <a href="{{ route('industry.index') }}" class="btn btn-secondary">رجوع</a>
    </form>

</div>
@endsection
