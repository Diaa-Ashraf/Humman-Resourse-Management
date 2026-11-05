@extends('dashboard.master')
@section('title','تعديل ملف الموظف')
@section('content')
@include('dashboard.employees.partials.form', ['employee' => $employee])
@endsection
