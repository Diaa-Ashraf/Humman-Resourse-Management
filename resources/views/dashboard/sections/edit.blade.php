@extends('dashboard.master')
@section('content')
@include('dashboard.sections.partials.form', ['section'=>$section])
@endsection
