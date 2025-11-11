@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $branch->name }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this branch? This action is irreversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
    
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Details & Stats</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="products-tab" data-toggle="tab" href="#products" role="tab">Products</a>
        </li>
    </ul>

    <div class="tab-content card" id="myTabContent">
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Branch Details</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Code:</th><td>{{ $branch->code }}</td></tr>
                            <tr><th>Status:</th><td>
                                @if($branch->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td></tr>
                            <tr><th>Address:</th><td>{{ $branch->address }}</td></tr>
                            <tr><th>City:</th><td>{{ $branch->city }}</td></tr>
                            <tr><th>Phone:</th><td>{{ $branch->phone }}</td></tr>
                            <tr><th>Email:</th><td>{{ $branch->email }}</td></tr>
                            <tr><th>Notes:</th><td>{{ $branch->notes }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Branch Stats</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Total Sales (All Time):</th><td>₱{{ number_format($stats['total_sales'], 2) }}</td></tr>
                            <tr><th>Total Sales (This Month):</th><td>₱{{ number_format($stats['monthly_sales'], 2) }}</td></tr>
                            <tr><th>Active Products:</th><td>{{ $stats['active_products'] }}</td></tr>
                            <tr><th>Low Stock Products:</th><td>{{ $stats['low_stock'] }}</td></tr>
                            <tr><th>Total Users:</th><td>{{ $stats['users_count'] }}</td></tr>
                        </table>

                        <hr>
                        <h4>Markup Settings</h4>
                        <form action="{{ route('branches.update-markup', $branch) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label for="markup_percentage">Default Markup Percentage</label>
                                <div class="input-group">
                                    <input type="number" name="markup_percentage" id="markup_percentage" class="form-control" value="{{ $branch->markup_percentage }}" step="0.01" min="0">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Update Markup</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branch->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->role->display_name }}</td>
                                <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No users assigned to this branch.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branch->products->take(50) as $product) <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>₱{{ number_format($product->selling_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No products in this branch.</td></tr>
                        @endforelse
                        @if($branch->products->count() > 50)
                            <tr><td colspan="4" class="text-center"><i>Showing first 50 products...</i></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection