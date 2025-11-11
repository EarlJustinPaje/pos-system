@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Payment Methods</h1>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-9">
                    <form action="{{ route('payment-methods.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Add New Method</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Fee (%)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentMethods as $method)
                            <tr>
                                <td>{{ $method->name }}</td>
                                <td><span class="badge badge-primary">{{ $method->code }}</span></td>
                                <td>{{ $method->type }}</td>
                                <td>{{ number_format($method->transaction_fee_percentage, 2) }}%</td>
                                <td>
                                    @if($method->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payment-methods.edit', $method) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('payment-methods.toggle-status', $method) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $method->is_active ? 'btn-secondary' : 'btn-success' }}">
                                            {{ $method->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No payment methods found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $paymentMethods->links() }}
            </div>
        </div>
    </div>
</div>
@endsection