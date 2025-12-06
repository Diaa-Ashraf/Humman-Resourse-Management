@extends('dashboard.master')

@section('content')

<div class="container mt-4">
    <h2>الشيفتات</h2>

```
<a href="{{ route('shifts.create') }}" class="btn btn-success mb-3">إضافة شيفت جديد</a>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>الاسم</th>
            <th>وقت البداية</th>
            <th>وقت النهاية</th>
            <th>الحالة</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->shift_name }}</td>
                <td>{{ $shift->start_time }}</td>
                <td>{{ $shift->end_time }}</td>
                <td>{{ $shift->status == 'open' ? 'مفتوح' : 'مغلق' }}</td>
                <td>
                    <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                    <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

</div>
@endsection
