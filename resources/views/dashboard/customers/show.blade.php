@extends('dashboard.master')

@section('title', 'عرض تفاصيل العميل')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تفاصيل العميل</h4>
                    <div class="float-left">
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm">
                            <i class="ft-edit-2"></i> تعديل
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ft-arrow-left"></i> رجوع
                        </a>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">معلومات أساسية</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>الاسم الكامل:</strong></td>
                                                <td>{{ $customer->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>الشركة:</strong></td>
                                                <td>{{ $customer->company ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>البريد الإلكتروني:</strong></td>
                                                <td>{{ $customer->email ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>الهاتف:</strong></td>
                                                <td>{{ $customer->phone }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>العنوان:</strong></td>
                                                <td>{{ $customer->address ?? 'غير محدد' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">معلومات إضافية</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>الصناعة:</strong></td>
                                                <td>{{ $customer->industry->name ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>الحالة:</strong></td>
                                                <td>
                                                    @switch($customer->status)
                                                        @case('active')
                                                            <span class="badge badge-success">نشط</span>
                                                            @break
                                                        @case('inactive')
                                                            <span class="badge badge-danger">غير نشط</span>
                                                            @break
                                                        @case('potential')
                                                            <span class="badge badge-warning">محتمل</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>تاريخ الإضافة:</strong></td>
                                                <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>آخر تحديث:</strong></td>
                                                <td>{{ $customer->updated_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($customer->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">ملاحظات</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $customer->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($customer->visits && $customer->visits->count() > 0)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">الزيارات السابقة</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>التاريخ</th>
                                                        <th>الغرض</th>
                                                        <th>النتيجة</th>
                                                        <th>ملاحظات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($customer->visits as $visit)
                                                        <tr>
                                                            <td>{{ $visit->visit_date }}</td>
                                                            <td>{{ $visit->purpose }}</td>
                                                            <td>{{ $visit->result }}</td>
                                                            <td>{{ $visit->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection