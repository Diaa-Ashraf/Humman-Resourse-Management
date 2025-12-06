@extends('dashboard.master')
@section('content')
<div class="container mt-4">
<h2 class="mb-3">bonuses Management</h2>


@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif


<a href="{{ route('bonuses.create') }}" class="btn btn-primary mb-3">Add bonus</a>


<table class="table table-bordered table-striped">
<thead>
<tr>
<th>ID</th>
<th>Employee</th>
<th>Amount</th>
<th>Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($bonuses as $bonus)
<tr>
<td>{{ $bonus->id }}</td>
<td>{{ $bonus->employee->name }}</td>
<td>{{ $bonus->amount }}</td>
<td>{{ $bonus->bonus_date }}</td>
<td>{{ ucfirst($bonus->status) }}</td>
<td>
<a href="{{ route('bonuses.edit', $bonus->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('bonuses.destroy', $bonus->id) }}" method="POST" style="display:inline-block">
@csrf
@method('DELETE')
<button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>


{{ $bonuses->links() }}
</div>
@endsection