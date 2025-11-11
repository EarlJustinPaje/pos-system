@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Payment Method: {{ $paymentMethod->name }}</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('payment-methods.update', $paymentMethod) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Method Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $paymentMethod->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="code">Method Code (e.g., GCASH, VISA, CASH)</label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $paymentMethod->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">Method Type</label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="cash" {{ old('type', $paymentMethod->type) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ old('type', $paymentMethod->type) == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                                <option value="digital_wallet" {{ old('type', $paymentMethod->type) == 'digital_wallet' ? 'selected' : '' }}>Digital Wallet/E-money</option>
                                <option value="other" {{ old('type', $paymentMethod->type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="transaction_fee_percentage">Transaction Fee Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" name="transaction_fee_percentage" id="transaction_fee_percentage" class="form-control @error('transaction_fee_percentage') is-invalid @enderror" value="{{ old('transaction_fee_percentage', $paymentMethod->transaction_fee_percentage) }}" step="0.01" min="0" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('transaction_fee_percentage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1" {{ $paymentMethod->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$paymentMethod->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="details">Integration/Account Details</label>
                    <textarea name="details" id="details" class="form-control @error('details') is-invalid @enderror">{{ old('details', $paymentMethod->details) }}</textarea>
                    <small class="form-text text-muted">e.g., Merchant ID, API Keys, or account numbers (for internal reference only).</small>
                    @error('details')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn btn-primary">Update Payment Method</button>
                <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection