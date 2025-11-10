@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Fast Moving Products</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="py-3">Product Name</th>
                        <th class="py-3">Stock</th>
                        <th class="py-3">Unit</th>
                        <th class="py-3">Manufacturer</th>
                        <th class="py-3">Capital Price</th>
                        <th class="py-3">Selling Price</th>
                        <th class="py-3">Sold Qty</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="px-4 py-3 fw-bold">#{{ $product->product_id }}</td>
                        <td class="py-3">
                            <div class="fw-bold">{{ $product->name }}</div>
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $product->quantity <= 10 ? 'bg-danger' : 'bg-success' }} fw-bold">
                                {{ $product->quantity }}
                            </span>
                        </td>
                        <td class="py-3 text-muted">{{ $product->unit }}</td>
                        <td class="py-3 text-muted">{{ $product->manufacturer }}</td>
                        <td class="py-3 fw-bold">₱{{ number_format($product->capital_price ?? 0, 2) }}</td>
                        <td class="py-3 fw-bold" style="color: var(--reddit-orange);">₱{{ number_format($product->selling_price ?? 0, 2) }}</td>
                        <td class="py-3">
                            <span class="badge bg-primary fw-bold">{{ $product->sold_quantity }}</span>
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} fw-bold">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <div class="btn-group">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                @if($product->is_active)
                                    <form method="POST" action="{{ route('products.deactivate', $product) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" 
                                                onclick="return confirm('Deactivate this product?')">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Delete this product?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <h5>No products found</h5>
                                <p>Add your first product to get started</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection