@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Promotion</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('promotions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Promotion Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="code">Promo Code (Optional)</label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                            <small class="form-text text-muted">Required only if this is a Coupon Code.</small>
                            @error('code')
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
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', now()->addMonth()->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="promotion_type">Promotion Type</label>
                            <select name="promotion_type" id="promotion_type" class="form-control @error('promotion_type') is-invalid @enderror" required>
                                <option value="" disabled selected>Select type</option>
                                <option value="percentage_discount" {{ old('promotion_type') == 'percentage_discount' ? 'selected' : '' }}>Percentage Discount (%)</option>
                                <option value="fixed_amount_discount" {{ old('promotion_type') == 'fixed_amount_discount' ? 'selected' : '' }}>Fixed Amount Discount (₱)</option>
                                <option value="buy_x_get_y" {{ old('promotion_type') == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y Free</option>
                            </select>
                            @error('promotion_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_value">Discount Value</label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value') }}" step="0.01" min="0" required>
                            <small class="form-text text-muted" id="discount_help">Enter percentage (e.g., 10 for 10%) or fixed amount.</small>
                            @error('discount_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="branch_id">Applicable Branch</label>
                    <select name="branch_id" id="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
                        <option value="">Global (All Branches)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr>
                
                <h4>Usage & Limits</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="min_purchase_amount">Minimum Purchase Amount (₱)</label>
                            <input type="number" name="min_purchase_amount" id="min_purchase_amount" class="form-control @error('min_purchase_amount') is-invalid @enderror" value="{{ old('min_purchase_amount', 0) }}" step="0.01" min="0">
                            @error('min_purchase_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_uses">Maximum Total Uses (0 for unlimited)</label>
                            <input type="number" name="max_uses" id="max_uses" class="form-control @error('max_uses') is-invalid @enderror" value="{{ old('max_uses', 0) }}" min="0">
                            @error('max_uses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Promotion</button>
                <a href="{{ route('promotions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple JavaScript to update the help text based on promotion type
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('promotion_type');
        const helpText = document.getElementById('discount_help');

        function updateHelpText() {
            const type = typeSelect.value;
            if (type === 'percentage_discount') {
                helpText.textContent = 'Enter the discount percentage (e.g., 10 for 10%).';
            } else if (type === 'fixed_amount_discount') {
                helpText.textContent = 'Enter the fixed discount amount (e.g., 100 for ₱100 off).';
            } else if (type === 'buy_x_get_y') {
                helpText.textContent = 'Enter the number of "X" items to buy (e.g., 2 for Buy 2 Get 1).';
            } else {
                helpText.textContent = 'Enter discount value based on the type selected.';
            }
        }

        typeSelect.addEventListener('change', updateHelpText);
        updateHelpText(); // Initialize on load
    });
</script>
@endsection