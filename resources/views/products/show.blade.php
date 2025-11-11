@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $product->name }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit Product</a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
    
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Details & Pricing</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="inventory-tab" data-toggle="tab" href="#inventory" role="tab">Inventory & History</a>
        </li>
    </ul>

    <div class="tab-content card" id="myTabContent">
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Identifiers</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>SKU:</th><td>{{ $product->sku ?? 'N/A' }}</td></tr>
                            <tr><th>Barcode:</th><td>{{ $product->barcode ?? 'N/A' }}</td></tr>
                            <tr><th>Category:</th><td>{{ $product->category->name ?? 'None' }}</td></tr>
                            <tr><th>Supplier:</th><td>{{ $product->supplier->name ?? 'None' }}</td></tr>
                            <tr><th>Manufacturer:</th><td>{{ $product->manufacturer ?? 'N/A' }}</td></tr>
                            <tr><th>Branch:</th><td>{{ $product->branch->name ?? 'Global' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h4>Pricing</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Capital Price:</th><td>₱{{ number_format($product->capital_price, 2) }}</td></tr>
                            <tr><th>Selling Price:</th><td>₱{{ number_format($product->selling_price, 2) }}</td></tr>
                            <tr><th>Markup %:</th><td>
                                {{ number_format($product->markup_percentage, 2) }}% 
                                @if($product->use_custom_markup)
                                    <span class="badge badge-warning">Custom</span>
                                @endif
                            </td></tr>
                            <tr><th>Profit per unit:</th><td>₱{{ number_format($product->selling_price - $product->capital_price, 2) }}</td></tr>
                            <tr><th>Status:</th><td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h4>Barcode/QR Code</h4>
                        @if($product->barcode_path)
                            <p><strong>Barcode:</strong> <a href="{{ Storage::url($product->barcode_path) }}" target="_blank">View Barcode</a></p>
                            <img src="{{ Storage::url($product->barcode_path) }}" style="max-width: 100%; height: auto; border: 1px solid #ccc;" alt="Barcode">
                            <a href="{{ route('products.print-barcode', $product) }}" class="btn btn-sm btn-secondary mt-2">Print Barcode</a>
                        @else
                            <p>No barcode image generated.</p>
                        @endif
                        <br>
                        @if($product->qr_code_path)
                            <p><strong>QR Code:</strong> <a href="{{ Storage::url($product->qr_code_path) }}" target="_blank">View QR Code</a></p>
                            <img src="{{ Storage::url($product->qr_code_path) }}" style="max-width: 100px; height: auto; border: 1px solid #ccc;" alt="QR Code">
                            <a href="{{ route('products.print-qrcode', $product) }}" class="btn btn-sm btn-secondary mt-2">Print QR Code</a>
                        @else
                            <p>No QR code image generated.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Current Stock</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Quantity:</th><td><h3 class="d-inline">{{ $product->quantity }}</h3> {{ $product->unit }}</td></tr>
                            <tr><th>Reorder Point:</th><td>{{ $product->reorder_point }}</td></tr>
                            <tr><th>Reorder Quantity:</th><td>{{ $product->reorder_quantity }}</td></tr>
                            <tr><th>Procurement Date:</th><td>{{ $product->date_procured->format('M d, Y') }}</td></tr>
                            <tr><th>Expiration Date:</th><td>{{ $product->expiration_date ? $product->expiration_date->format('M d, Y') : 'N/A' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <h4>Sales History (Last 30 Days)</h4>
                        <p class="text-muted">A table of recent sales and inventory movements would appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection