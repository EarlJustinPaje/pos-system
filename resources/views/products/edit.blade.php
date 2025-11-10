@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Update Product</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product->product_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @include('layouts.partials.messages')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manufacturer</label>
                            <input type="text" name="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" 
                                   value="{{ old('manufacturer', $product->manufacturer) }}" required>
                            @error('manufacturer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity', $product->quantity) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" 
                                   value="{{ old('unit', $product->unit) }}" placeholder="e.g., pcs, kg, liter" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Capital Price</label>
                            <input type="number" step="0.01" name="capital_price" 
                                   class="form-control @error('capital_price') is-invalid @enderror" 
                                   value="{{ old('capital_price', $product->capital_price ?? $product->price) }}" required>
                            @error('capital_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Selling price will be 15% higher</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date Procured</label>
                            <input type="date" name="date_procured" class="form-control @error('date_procured') is-invalid @enderror" 
                                   value="{{ old('date_procured', $product->date_procured->format('Y-m-d')) }}" required>
                            @error('date_procured')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Manufactured Date</label>
                            <input type="date" name="manufactured_date" class="form-control @error('manufactured_date') is-invalid @enderror" 
                                   value="{{ old('manufactured_date', $product->manufactured_date->format('Y-m-d')) }}" required>
                            @error('manufactured_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expiration Date (Optional)</label>
                            <input type="date" name="expiration_date" class="form-control @error('expiration_date') is-invalid @enderror" 
                                   value="{{ old('expiration_date', $product->expiration_date?->format('Y-m-d')) }}">
                            @error('expiration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection