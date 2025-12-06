@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <h3 class="mb-3">تعديل التخصص</h3>

    <form action="{{ route('industry.update', $industry->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">اسم التخصص</label>
            <input type="text" name="name" class="form-control" value="{{ $industry->name }}">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button class="btn btn-warning">تحديث</button>
        <a href="{{ route('industry.index') }}" class="btn btn-secondary">رجوع</a>
    </form>

</div>
@endsection
