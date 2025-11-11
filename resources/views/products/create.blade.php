@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Product</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                
                <h4>Basic Information</h4>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="unit">Unit of Measure (e.g., pc, box, liter)</label>
                            <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', 'pc') }}" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr>
                <h4>Categorization & Sourcing</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="supplier_id">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="form-group">
                            <label for="branch_id">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <hr>
                <h4>Pricing & Inventory</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="capital_price">Capital/Cost Price (₱)</label>
                            <input type="number" name="capital_price" id="capital_price" class="form-control @error('capital_price') is-invalid @enderror" value="{{ old('capital_price') }}" step="0.01" min="0" required>
                            @error('capital_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="markup_percentage">Markup Percentage (%)</label>
                            <input type="number" name="markup_percentage" id="markup_percentage" class="form-control @error('markup_percentage') is-invalid @enderror" value="{{ old('markup_percentage', $defaultMarkup) }}" step="0.01" min="0">
                            @error('markup_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" id="use_custom_markup" name="use_custom_markup" value="1" {{ old('use_custom_markup') ? 'checked' : '' }}>
                                <label class="form-check-label" for="use_custom_markup">Use custom markup (Overrides Branch Default)</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="selling_price">Selling Price (₱)</label>
                            <input type="number" name="selling_price" id="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" step="0.01" min="0">
                            <small class="form-text text-muted">Auto-calculated based on markup unless manually entered.</small>
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Initial Stock Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 0) }}" min="0" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reorder_point">Reorder Point</label>
                            <input type="number" name="reorder_point" id="reorder_point" class="form-control @error('reorder_point') is-invalid @enderror" value="{{ old('reorder_point', 5) }}" min="0">
                            <small class="form-text text-muted">When stock drops to this level, an alert is triggered.</small>
                            @error('reorder_point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reorder_quantity">Reorder Quantity</label>
                            <input type="number" name="reorder_quantity" id="reorder_quantity" class="form-control @error('reorder_quantity') is-invalid @enderror" value="{{ old('reorder_quantity', 10) }}" min="0">
                            <small class="form-text text-muted">Suggested quantity to order.</small>
                            @error('reorder_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <hr>
                <h4>Identifiers & Dates</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}">
                            <small class="form-text text-muted">Stock Keeping Unit (Unique Identifier)</small>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="barcode">Barcode</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}">
                            <small class="form-text text-muted">EAN/UPC/Code 128 (Unique)</small>
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manufacturer">Manufacturer</label>
                            <input type="text" name="manufacturer" id="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" value="{{ old('manufacturer') }}">
                            @error('manufacturer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manufactured_date">Manufactured Date</label>
                            <input type="date" name="manufactured_date" id="manufactured_date" class="form-control @error('manufactured_date') is-invalid @enderror" value="{{ old('manufactured_date') }}">
                            @error('manufactured_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_procured">Date Procured (Inventory Date)</label>
                            <input type="date" name="date_procured" id="date_procured" class="form-control @error('date_procured') is-invalid @enderror" value="{{ old('date_procured', now()->format('Y-m-d')) }}">
                            @error('date_procured')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiration_date">Expiration Date (Optional)</label>
                            <input type="date" name="expiration_date" id="expiration_date" class="form-control @error('expiration_date') is-invalid @enderror" value="{{ old('expiration_date') }}">
                            @error('expiration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Add Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple logic to calculate selling price based on capital price and markup
    document.addEventListener('DOMContentLoaded', function() {
        const capitalPriceInput = document.getElementById('capital_price');
        const markupInput = document.getElementById('markup_percentage');
        const sellingPriceInput = document.getElementById('selling_price');
        const useCustomMarkupCheckbox = document.getElementById('use_custom_markup');

        function calculateSellingPrice() {
            const capital = parseFloat(capitalPriceInput.value);
            const markup = parseFloat(markupInput.value);

            if (isNaN(capital) || isNaN(markup) || capital < 0 || markup < 0) {
                return;
            }

            const sellingPrice = capital * (1 + (markup / 100));
            sellingPriceInput.value = sellingPrice.toFixed(2);
        }

        capitalPriceInput.addEventListener('input', calculateSellingPrice);
        markupInput.addEventListener('input', calculateSellingPrice);
        
        // Prevent manual entry of selling price if not using custom markup
        function toggleSellingPriceInput() {
            if (!useCustomMarkupCheckbox.checked) {
                 // If not checked, recalculate and make sure selling price is tied to formula
                 calculateSellingPrice();
            }
        }
        
        // Initial setup for selling price calculation
        calculateSellingPrice();
    });
</script>
@endsection