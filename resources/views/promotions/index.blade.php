@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Promotions & Discounts</h1>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-9">
                    <form action="{{ route('promotions.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, code, or type..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('promotions.create') }}" class="btn btn-primary">Create New Promotion</a>
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
                            <th>Value</th>
                            <th>Period</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promotion)
                            <tr>
                                <td>{{ $promotion->name }}</td>
                                <td><span class="badge badge-secondary">{{ $promotion->code }}</span></td>
                                <td>{{ $promotion->getTypeLabel() }}</td>
                                <td>{{ $promotion->getDisplayValue() }}</td>
                                <td>
                                    {{ $promotion->start_date->format('M d, Y') }} - {{ $promotion->end_date->format('M d, Y') }}
                                </td>
                                <td>{{ $promotion->branch->name ?? 'Global' }}</td>
                                <td>
                                    @if($promotion->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('promotions.show', $promotion) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('promotions.toggle-status', $promotion) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $promotion->is_active ? 'btn-secondary' : 'btn-success' }}">
                                            {{ $promotion->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No promotions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $promotions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection