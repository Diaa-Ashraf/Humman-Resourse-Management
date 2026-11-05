@extends('dashboard.master')
@section('content')
@include('dashboard.LeaveTypes.partials.form',['leaveType'=>$leaveType])
@endsection
