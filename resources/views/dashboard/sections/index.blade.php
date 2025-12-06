@extends('dashboard.master')

@section('content')
<div class="container mt-4">
    <h2>الأقسام</h2>

    <a href="{{ route('sections.create') }}" class="btn btn-success mb-3">إضافة قسم جديد</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم القسم</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sections as $section)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $section->name }}</td>
                <td>
                    <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-primary btn-sm">تعديل</a>

                    <form action="{{ route('sections.destroy', $section->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $sections->links() }}
</div>
@endsection
