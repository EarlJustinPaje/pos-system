@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Product Inventory</h1>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search name, SKU, or barcode..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a href="{{ route('products.create') }}" class="btn btn-success">Add New Product</a>
                        @if(auth()->user()->hasPermission(\App\Models\Permission::IMPORT_PRODUCTS))
                            <a href="{{ route('products.imports.create') }}" class="btn btn-info">Bulk Import</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU/Barcode</th>
                            <th>Category</th>
                            <th>Branch</th>
                            <th>Stock</th>
                            <th>Cost</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <strong>SKU:</strong> {{ $product->sku ?? 'N/A' }} <br>
                                    <strong>Barcode:</strong> {{ $product->barcode ?? 'N/A' }}
                                </td>
                                <td>{{ $product->category->name ?? 'None' }}</td>
                                <td>{{ $product->branch->name ?? 'Global' }}</td>
                                <td>
                                    {{ $product->quantity }} {{ $product->unit }}
                                    @if($product->isLowStock())
                                        <span class="badge badge-danger">Low!</span>
                                    @endif
                                </td>
                                <td>₱{{ number_format($product->capital_price, 2) }}</td>
                                <td>₱{{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No products found matching your criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection