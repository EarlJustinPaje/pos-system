@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Product Search -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Product Search</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="productSearch" class="form-control" placeholder="Search products...">
                </div>
                <div id="searchResults" class="mb-3"></div>
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="number" id="quantity" class="form-control" placeholder="Quantity" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <button id="addToCart" class="btn btn-primary w-100" disabled>Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shopping Cart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Shopping Cart</h5>
            </div>
            <div class="card-body">
                <div id="cartItems">
                    <p class="text-muted">Cart is empty</p>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6"><strong>Total:</strong></div>
                    <div class="col-6 text-end"><strong id="totalAmount">₱0.00</strong></div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <input type="number" id="cashReceived" class="form-control" placeholder="Cash Received" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <button id="checkoutBtn" class="btn btn-success w-100" disabled>Checkout</button>
                    </div>
                </div>
                
                <div id="change" class="mt-2 text-success fw-bold" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let cart = [];
let selectedProduct = null;

$(document).ready(function() {
    // Product search
    $('#productSearch').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchProducts(query);
        } else {
            $('#searchResults').empty();
            selectedProduct = null;
            $('#addToCart').prop('disabled', true);
        }
    });

    // Add to cart
    $('#addToCart').click(function() {
        if (selectedProduct) {
            addToCart(selectedProduct, $('#quantity').val());
        }
    });

    // Cash received input
    $('#cashReceived').on('input', function() {
        const cash = parseFloat($(this).val()) || 0;
        const total = getTotalAmount();
        
        if (cash >= total && cart.length > 0) {
            $('#checkoutBtn').prop('disabled', false);
            const change = cash - total;
            $('#change').text(`Change: ₱${change.toFixed(2)}`).show();
        } else {
            $('#checkoutBtn').prop('disabled', true);
            $('#change').hide();
        }
    });

    // Checkout
    $('#checkoutBtn').click(function() {
        checkout();
    });
});

function searchProducts(query) {
    $.get('/pos/search', { q: query }, function(products) {
        let html = '';
        products.forEach(product => {
            html += `
                <div class="border p-2 mb-2 product-item" data-product='${JSON.stringify(product)}' style="cursor: pointer;">
                    <strong>${product.name}</strong><br>
                    <small class="text-muted">${product.manufacturer}</small><br>
                    <small>₱${product.price.toFixed(2)} | Stock: ${product.available_quantity} ${product.unit}</small>
                </div>
            `;
        });
        $('#searchResults').html(html);

        $('.product-item').click(function() {
            $('.product-item').removeClass('bg-primary text-white');
            $(this).addClass('bg-primary text-white');
            selectedProduct = JSON.parse($(this).attr('data-product'));
            $('#addToCart').prop('disabled', false);
        });
    });
}

function addToCart(product, quantity) {
    quantity = parseInt(quantity);
    
    if (quantity <= 0 || quantity > product.available_quantity) {
        alert('Invalid quantity');
        return;
    }

    const existingItem = cart.find(item => item.product.id === product.id);
    
    if (existingItem) {
        if (existingItem.quantity + quantity > product.available_quantity) {
            alert('Not enough stock');
            return;
        }
        existingItem.quantity += quantity;
        existingItem.total = existingItem.quantity * product.price;
    } else {
        cart.push({
            product: product,
            quantity: quantity,
            total: quantity * product.price
        });
    }

    updateCartDisplay();
    $('#quantity').val(1);
    selectedProduct = null;
    $('#addToCart').prop('disabled', true);
    $('#productSearch').val('');
    $('#searchResults').empty();
}

function updateCartDisplay() {
    if (cart.length === 0) {
        $('#cartItems').html('<p class="text-muted">Cart is empty</p>');
        $('#totalAmount').text('₱0.00');
        return;
    }

    let html = '';
    cart.forEach((item, index) => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                <div>
                    <strong>${item.product.name}</strong><br>
                    <small>₱${item.product.price.toFixed(2)} × ${item.quantity}</small>
                </div>
                <div>
                    <strong>₱${item.total.toFixed(2)}</strong>
                    <button class="btn btn-sm btn-danger ms-2" onclick="removeFromCart(${index})">×</button>
                </div>
            </div>
        `;
    });

    $('#cartItems').html(html);
    $('#totalAmount').text(`₱${getTotalAmount().toFixed(2)}`);
    
    // Update checkout button state
    const cash = parseFloat($('#cashReceived').val()) || 0;
    const total = getTotalAmount();
    $('#checkoutBtn').prop('disabled', cash < total || cart.length === 0);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function getTotalAmount() {
    return cart.reduce((total, item) => total + item.total, 0);
}

function checkout() {
    const cashReceived = parseFloat($('#cashReceived').val());
    const items = cart.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity
    }));

    $.ajax({
        url: '/pos/checkout',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            items: items,
            cash_received: cashReceived
        },
        success: function(response) {
            if (response.success) {
                alert(`Transaction completed!\nChange: ₱${response.change.toFixed(2)}`);
                cart = [];
                updateCartDisplay();
                $('#cashReceived').val('');
                $('#change').hide();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Transaction failed';
            alert('Error: ' + error);
        }
    });
}
</script>
@endpush
@endsection
