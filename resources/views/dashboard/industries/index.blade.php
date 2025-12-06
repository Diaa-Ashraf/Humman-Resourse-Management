@extends('dashboard.master')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>التخصصات</h3>
        <a href="{{ route('industry.create') }}" class="btn btn-primary">إضافة تخصص</a>
    </div>

    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم التخصص</th>
                <th>العمليات</th>
            </tr>
        </thead>

        <tbody>
            @foreach($industries as $industry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $industry->name }}</td>
                    <td>
                        {{-- <a href="{{ route('industry.show', $industry->id) }}" class="btn btn-info btn-sm">عرض</a> --}}
                        <a href="{{ route('industry.edit', $industry->id) }}" class="btn btn-warning btn-sm">تعديل</a>

                        <form action="{{ route('industry.destroy', $industry->id) }}" 
                              method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</div>
@endsection
