@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Branches</h1>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-9">
                    <form action="{{ route('branches.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, code, or city..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('branches.create') }}" class="btn btn-primary">Add New Branch</a>
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
                            <th>City</th>
                            <th>Users</th>
                            <th>Products</th>
                            <th>Markup</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->code }}</td>
                                <td>{{ $branch->city }}</td>
                                <td>{{ $branch->users_count }}</td>
                                <td>{{ $branch->products_count }}</td>
                                <td>{{ $branch->markup_percentage }}%</td>
                                <td>
                                    @if($branch->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('branches.show', $branch) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No branches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $branches->links() }}
            </div>
        </div>
    </div>
</div>
@endsection