@extends('dashboard.master')

@section('title', 'تعديل العميل')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تعديل العميل: {{ $customer->name }}</h4>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm float-left">
                        <i class="ft-arrow-left"></i> رجوع
                    </a>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">الاسم الكامل <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="{{ old('name', $customer->name) }}" required>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company">الشركة</label>
                                        <input type="text" class="form-control" id="company" name="company" 
                                               value="{{ old('company', $customer->company) }}">
                                        @error('company')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">البريد الإلكتروني</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="{{ old('email', $customer->email) }}">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">الهاتف <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="{{ old('phone', $customer->phone) }}" required>
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="industry_id">الصناعة</label>
                                        <select class="form-control" id="industry_id" name="industry_id">
                                            <option value="">اختر الصناعة</option>
                                            @foreach($industries as $industry)
                                                <option value="{{ $industry->id }}" 
                                                        @if(old('industry_id', $customer->industry_id) == $industry->id) selected @endif>
                                                    {{ $industry->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('industry_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">الحالة <span class="text-danger">*</span></label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="">اختر الحالة</option>
                                            <option value="active" @if(old('status', $customer->status) == 'active') selected @endif>نشط</option>
                                            <option value="inactive" @if(old('status', $customer->status) == 'inactive') selected @endif>غير نشط</option>
                                            <option value="potential" @if(old('status', $customer->status) == 'potential') selected @endif>محتمل</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="address">العنوان</label>
                                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                                        @error('address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes">ملاحظات</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                                        @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ft-save"></i> تحديث
                                    </button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                        <i class="ft-x"></i> إلغاء
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection