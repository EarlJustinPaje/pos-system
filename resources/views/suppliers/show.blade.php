@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $supplier->name }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
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
            <a class="nav-link" id="products-tab" data-toggle="tab" href="#products" role="tab">Products</a>
        </li>
    </ul>

    <div class="tab-content card" id="myTabContent">
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Contact Details</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Code:</th><td>{{ $supplier->code }}</td></tr>
                            <tr><th>Contact Person:</th><td>{{ $supplier->contact_person }}</td></tr>
                            <tr><th>Email:</th><td>{{ $supplier->email }}</td></tr>
                            <tr><th>Phone:</th><td>{{ $supplier->phone }}</td></tr>
                            <tr><th>Mobile:</th><td>{{ $supplier->mobile }}</td></tr>
                            <tr><th>Address:</th><td>{{ $supplier->address }}</td></tr>
                            <tr><th>City:</th><td>{{ $supplier->city }}</td></tr>
                            <tr><th>Country:</th><td>{{ $supplier->country }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Financial Details & Stats</h4>
                        <table class="table table-sm table-borderless">
                            <tr><th>Tax ID (TIN):</th><td>{{ $supplier->tax_id }}</td></tr>
                            <tr><th>Payment Terms:</th><td>{{ $supplier->getPaymentTermsLabel() }}</td></tr>
                            <tr><th>Status:</th><td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td></tr>
                            <tr><th>Total Products:</th><td>{{ $stats['total_products'] }}</td></tr>
                            <tr><th>Active Products:</th><td>{{ $stats['active_products'] }}</td></tr>
                            <tr><th>Notes:</th><td>{{ $supplier->notes }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Branch</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->products->take(50) as $product) <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->branch->name ?? 'N/A' }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>₱{{ number_format($product->selling_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No products supplied by this vendor.</td></tr>
                        @endforelse
                        @if($supplier->products->count() > 50)
                            <tr><td colspan="5" class="text-center"><i>Showing first 50 products...</i></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection