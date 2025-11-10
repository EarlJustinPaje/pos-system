@extends('layouts.app')

@section('content')
<h1>Inventory Reports</h1>

<!-- Fast Moving Products -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Fast Moving Products (Top 20)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold Quantity</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fastMoving as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sold_quantity }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit }}</td>
                        <td>
                            @if($product->quantity <= 10)
                                <span class="badge bg-warning">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Expired Products -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Expired Products</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Expiration Date</th>
                        <th>Current Stock</th>
                        <th>Days Expired</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expired as $product)
                    <tr class="table-danger">
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->expiration_date->format('Y-m-d') }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit }}</td>
                        <td>{{ $product->expiration_date->diffInDays(now()) }} days</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No expired products</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Near Expiring Products -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Near to Expire Products (Within 30 days)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Expiration Date</th>
                        <th>Current Stock</th>
                        <th>Days Until Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nearExpiring as $product)
                    <tr class="table-warning">
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->expiration_date->format('Y-m-d') }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit }}</td>
                        <td>{{ now()->diffInDays($product->expiration_date) }} days</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No products near expiry</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- All Products Inventory -->
<div class="card">
    <div class="card-header">
        <h5>Product Inventory</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Manufacturer</th>
                        <th>Current Stock</th>
                        <th>Sold Quantity</th>
                        <th>Capital Price</th>
                        <th>Selling Price</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allProducts as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->manufacturer }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit }}</td>
                        <td>{{ $product->sold_quantity }}</td>
                        <td>₱{{ number_format($product->capital_price, 2) }}</td>
                        <td>₱{{ number_format($product->selling_price, 2) }}</td>
                        <td>₱{{ number_format($product->quantity * $product->capital_price, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No products found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection