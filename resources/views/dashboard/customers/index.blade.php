@extends('dashboard.master')

@section('title', 'العملاء')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">قائمة العملاء</h4>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm float-left">
                        <i class="ft-plus"></i> إضافة عميل جديد
                    </a>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الشركة</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الهاتف</th>
                                        <th>الصناعة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $customer)
                                        <tr>
                                            <td>{{ $customer->id }}</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->company ?? '-' }}</td>
                                            <td>{{ $customer->email ?? '-' }}</td>
                                            <td>{{ $customer->phone }}</td>
                                            <td>{{ $customer->industry->name ?? '-' }}</td>
                                           <td>
    @php
        $status = strtolower($customer->status);
    @endphp

    @switch($status)
        @case('active')
            <span class="badge badge-success">نشط</span>
            @break

        @case('inactive')
            <span class="badge badge-danger">غير نشط</span>
            @break

        @case('potential')
            <span class="badge badge-warning">محتمل</span>
            @break

        @default
            <span class="badge badge-secondary">{{ $customer->status }}</span>
    @endswitch
</td>

                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('customers.show', $customer->id) }}" 
                                                       class="btn btn-info btn-sm" title="عرض">
                                                        <i class="ft-eye"></i>
                                                    </a>
                                                    <a href="{{ route('customers.edit', $customer->id) }}" 
                                                       class="btn btn-warning btn-sm" title="تعديل">
                                                        <i class="ft-edit-2"></i>
                                                    </a>
                                                    <form action="{{ route('customers.destroy', $customer->id) }}" 
                                                          method="POST" style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                                            <i class="ft-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد عملاء حالياً</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $customers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection