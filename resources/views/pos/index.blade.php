@extends('layouts.app')

@section('content')
<div class="container-fluid pos-container">
    <div class="row h-100">
        
        <div class="col-md-6 pos-left-panel">
            <div class="card h-100 d-flex flex-column">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5>Transaction ({{ auth()->user()->branch->name ?? 'Global' }})</h5>
                        </div>
                        <div class="col-6 text-right">
                            <button id="new-transaction" class="btn btn-sm btn-light">New Transaction</button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body flex-grow-1 overflow-auto p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Product</th>
                                <th style="width: 15%;">Price</th>
                                <th style="width: 15%;">Qty</th>
                                <th style="width: 15%;">Total</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <tr id="empty-cart-row"><td colspan="6" class="text-center text-muted py-5">Scan a barcode or search for a product to begin.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer p-2">
                    <div class="row">
                        <div class="col-6">Total Items: <strong id="total-items">0</strong></div>
                        <div class="col-6 text-right">Subtotal: <strong id="subtotal">₱0.00</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-6">Discount: <strong id="total-discount" class="text-danger">₱0.00</strong></div>
                        <div class="col-6 text-right">Tax (VAT): <strong id="tax-amount">₱0.00</strong></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#discountModal">Add Discount</button>
                        </div>
                        <div class="col-6 text-right">
                            <h3>Total Due: <strong id="final-amount">₱0.00</strong></h3>
                        </div>
                    </div>

                    <button id="checkout-btn" class="btn btn-success btn-lg btn-block mt-2" data-toggle="modal" data-target="#checkoutModal" disabled>
                        Checkout (F1)
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 pos-right-panel">
            <div class="card h-100 d-flex flex-column">
                <div class="card-header">
                    <div class="input-group">
                        <input type="text" id="barcode-input" class="form-control form-control-lg" placeholder="Scan Barcode or Enter Product Name" autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#searchModal">Search (F2)</button>
                        </div>
                    </div>
                </div>
                <div class="card-body overflow-auto">
                    <h5>Quick Category Select</h5>
                    <div class="btn-group-sm mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary quick-category-btn" data-category-id="">All</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-outline-primary quick-category-btn" data-category-id="{{ $category->id }}">{{ $category->name }}</button>
                        @endforeach
                    </div>
                    
                    <div id="product-grid" class="row">
                        <div class="col-12 text-center text-muted p-5" id="product-grid-placeholder">Loading quick access products...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="checkoutModalLabel">Complete Transaction</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="checkout-form">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Final Amount: <strong class="text-success" id="modal-final-amount">₱0.00</strong></h4>
                            
                            <div class="form-group">
                                <label for="payment-method">Payment Method</label>
                                <select id="payment-method-select" class="form-control" required>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" data-type="{{ $method->type }}" data-fee="{{ $method->transaction_fee_percentage }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group" id="cash-input-group">
                                <label for="amount-tendered">Amount Tendered</label>
                                <input type="number" id="amount-tendered" class="form-control form-control-lg" step="0.01" min="0" required>
                                <small class="form-text text-muted">Use the quick buttons below or enter manually.</small>
                            </div>

                            <div class="row mb-3" id="quick-cash-buttons">
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" data-amount="50">₱50</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" data-amount="100">₱100</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" data-amount="500">₱500</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" data-amount="1000">₱1000</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" id="quick-exact">Exact Amount</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-secondary btn-block quick-tender" data-amount="0" id="quick-custom">Custom</button></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer-name">Customer Name (Optional)</label>
                                <input type="text" id="customer-name" class="form-control">
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info text-center py-4">
                                <h5>Payment Summary</h5>
                                <p>Total: <strong id="checkout-summary-total">₱0.00</strong></p>
                                <p>Tendered: <strong id="checkout-summary-tendered">₱0.00</strong></p>
                                <hr>
                                <h4>Change: <strong id="checkout-summary-change" class="text-danger">₱0.00</strong></h4>
                            </div>
                            
                            <div id="card-details-group" style="display:none;">
                                <div class="form-group">
                                    <label for="reference-number">Card Reference / Trace No.</label>
                                    <input type="text" id="reference-number" class="form-control">
                                </div>
                            </div>

                            <div id="digital-wallet-group" style="display:none;">
                                <div class="form-group">
                                    <label for="wallet-reference">Digital Wallet Ref.</label>
                                    <input type="text" id="wallet-reference" class="form-control">
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="checkout-form" class="btn btn-success" id="process-payment-btn" disabled>
                    Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="discountModalLabel">Apply Discount</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="promo-code-input">Promo Code</label>
                    <input type="text" id="promo-code-input" class="form-control" placeholder="Enter promo code">
                </div>
                <button id="apply-promo-btn" class="btn btn-info btn-block">Apply Code</button>
                <hr>
                <div class="form-group">
                    <label for="fixed-discount-input">Manual Fixed Discount (₱)</label>
                    <input type="number" id="fixed-discount-input" class="form-control" step="0.01" min="0">
                </div>
                <button id="apply-fixed-discount-btn" class="btn btn-secondary btn-block">Apply Fixed Discount</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="clear-discount-btn" data-dismiss="modal">Clear Discount</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item: <span id="modal-item-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-item-id">
                <div class="form-group">
                    <label for="modal-quantity">Quantity</label>
                    <input type="number" id="modal-quantity" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label for="modal-price">Price Per Unit</label>
                    <input type="number" id="modal-price" class="form-control" step="0.01" min="0" required>
                </div>
                <small class="form-text text-muted">Stock available: <span id="modal-stock-qty"></span></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-item-edit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const VAT_RATE = {{ \App\Models\Setting::getVatRate() / 100 }}; // e.g., 0.12
        let cart = []; // Array of cart items
        let discount = { type: 'none', value: 0, code: null };

        // Helper functions
        const formatCurrency = (amount) => `₱${parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
        const calculateTotals = () => {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            let totalDiscount = 0;
            let netSubtotal = subtotal;

            if (discount.type === 'fixed_amount') {
                totalDiscount = discount.value;
            } else if (discount.type === 'percentage') {
                totalDiscount = subtotal * (discount.value / 100);
            }
            // Add fixed buy_x_get_y logic here if needed, but it's simpler to apply it item-by-item on item price update.

            netSubtotal = subtotal - totalDiscount;
            if (netSubtotal < 0) netSubtotal = 0;

            const taxAmount = netSubtotal * VAT_RATE;
            const finalAmount = netSubtotal + taxAmount;

            // Update UI
            document.getElementById('total-items').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('total-discount').textContent = formatCurrency(totalDiscount);
            document.getElementById('tax-amount').textContent = formatCurrency(taxAmount);
            document.getElementById('final-amount').textContent = formatCurrency(finalAmount);
            document.getElementById('modal-final-amount').textContent = formatCurrency(finalAmount);

            // Enable/Disable Checkout button
            document.getElementById('checkout-btn').disabled = cart.length === 0;

            return { finalAmount, totalDiscount, taxAmount, subtotal };
        };

        const renderCart = () => {
            const cartItemsBody = document.getElementById('cart-items');
            cartItemsBody.innerHTML = '';

            if (cart.length === 0) {
                cartItemsBody.innerHTML = '<tr id="empty-cart-row"><td colspan="6" class="text-center text-muted py-5">Scan a barcode or search for a product to begin.</td></tr>';
            } else {
                cart.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>
                            <strong>${item.name}</strong> 
                            <span class="text-muted d-block small">${item.sku || item.barcode}</span>
                        </td>
                        <td>${formatCurrency(item.price)}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm cart-qty-input" data-id="${item.id}" value="${item.quantity}" min="1" max="${item.stock_qty}" style="width: 70px; display: inline-block;">
                        </td>
                        <td><strong>${formatCurrency(item.price * item.quantity)}</strong></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning edit-item-btn" data-id="${item.id}" data-toggle="modal" data-target="#editItemModal"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger remove-item-btn" data-id="${item.id}"><i class="fa fa-trash"></i></button>
                        </td>
                    `;
                    cartItemsBody.appendChild(row);
                });
            }
            calculateTotals();
        };

        const addItemToCart = (product, quantity = 1) => {
            const existingItem = cart.find(item => item.id === product.id);

            if (existingItem) {
                if (existingItem.quantity + quantity > existingItem.stock_qty) {
                    alert(`Cannot add ${quantity} units. Only ${existingItem.stock_qty - existingItem.quantity} left in stock.`);
                    return;
                }
                existingItem.quantity += quantity;
            } else {
                if (quantity > product.quantity) {
                     alert(`Cannot add ${quantity} units. Only ${product.quantity} left in stock.`);
                     quantity = product.quantity; // Cap at max stock
                     if (quantity === 0) return;
                }

                cart.push({
                    id: product.id,
                    product_id: product.id,
                    name: product.name,
                    sku: product.sku,
                    barcode: product.barcode,
                    price: parseFloat(product.selling_price), // Base price
                    quantity: quantity,
                    stock_qty: product.quantity, // Max quantity allowed
                });
            }
            renderCart();
        };

        const updateCartItem = (itemId, newQuantity, newPrice) => {
            const item = cart.find(item => item.id == itemId);
            if (item) {
                if (newQuantity > item.stock_qty) {
                    alert(`Cannot exceed stock quantity of ${item.stock_qty}.`);
                    newQuantity = item.stock_qty;
                }
                item.quantity = parseInt(newQuantity);
                item.price = parseFloat(newPrice);
                renderCart();
            }
        };

        // Event Listeners for Cart Actions
        document.getElementById('cart-items').addEventListener('change', function(e) {
            if (e.target.classList.contains('cart-qty-input')) {
                const itemId = e.target.getAttribute('data-id');
                const newQty = parseInt(e.target.value);
                const item = cart.find(i => i.id == itemId);

                if (newQty > item.stock_qty) {
                    alert(`Cannot exceed stock quantity of ${item.stock_qty}.`);
                    e.target.value = item.quantity;
                    return;
                }

                if (item && newQty >= 1) {
                    item.quantity = newQty;
                } else if (item && newQty < 1) {
                    // Remove if quantity set to 0
                    cart = cart.filter(i => i.id != itemId);
                }
                renderCart();
            }
        });

        document.getElementById('cart-items').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                const itemId = e.target.closest('.remove-item-btn').getAttribute('data-id');
                cart = cart.filter(item => item.id != itemId);
                renderCart();
            }
            if (e.target.closest('.edit-item-btn')) {
                const itemId = e.target.closest('.edit-item-btn').getAttribute('data-id');
                const item = cart.find(i => i.id == itemId);
                if (item) {
                    document.getElementById('modal-item-id').value = item.id;
                    document.getElementById('modal-item-name').textContent = item.name;
                    document.getElementById('modal-quantity').value = item.quantity;
                    document.getElementById('modal-price').value = item.price;
                    document.getElementById('modal-stock-qty').textContent = item.stock_qty;
                }
            }
        });
        
        document.getElementById('save-item-edit').addEventListener('click', function() {
            const itemId = document.getElementById('modal-item-id').value;
            const newQty = document.getElementById('modal-quantity').value;
            const newPrice = document.getElementById('modal-price').value;

            if (newQty && newPrice) {
                updateCartItem(itemId, newQty, newPrice);
                $('#editItemModal').modal('hide');
            }
        });

        // Barcode Scanning / Product Search
        document.getElementById('barcode-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const barcode = e.target.value.trim();
                e.target.value = ''; // Clear input immediately

                if (barcode) {
                    fetch('{{ route('pos.search-barcode') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ barcode: barcode })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addItemToCart(data.product);
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred during barcode scan.');
                    });
                }
            }
        });

        // Product Grid Loading (Right Panel)
        const loadProductGrid = (search = '', categoryId = '') => {
            const grid = document.getElementById('product-grid');
            grid.innerHTML = '<div class="col-12 text-center text-muted p-5">Loading...</div>';

            fetch(`{{ route('pos.search-products') }}?search=${search}&category_id=${categoryId}`)
                .then(response => response.json())
                .then(products => {
                    grid.innerHTML = '';
                    if (products.length === 0) {
                        grid.innerHTML = '<div class="col-12 text-center text-muted p-5">No products found.</div>';
                        return;
                    }
                    products.forEach(product => {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-sm-4 col-lg-3 mb-3';
                        col.innerHTML = `
                            <div class="card product-card" data-id="${product.id}">
                                <div class="card-body p-2 text-center">
                                    <h6 class="card-title mb-0">${product.name}</h6>
                                    <p class="card-text text-success mb-0"><strong>${formatCurrency(product.selling_price)}</strong></p>
                                    <p class="card-text text-muted small mb-1">${product.quantity} in stock</p>
                                    <button class="btn btn-sm btn-primary add-to-cart-btn" data-product='${JSON.stringify(product)}'>Add</button>
                                </div>
                            </div>
                        `;
                        grid.appendChild(col);
                    });
                });
        };
        
        // Initial load
        loadProductGrid();
        
        // Quick Category Buttons
        document.querySelectorAll('.quick-category-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.quick-category-btn').forEach(btn => btn.classList.remove('btn-primary'));
                this.classList.add('btn-primary');
                loadProductGrid('', this.getAttribute('data-category-id'));
            });
        });

        // Add to cart from grid
        document.getElementById('product-grid').addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart-btn')) {
                const productData = JSON.parse(e.target.getAttribute('data-product'));
                addItemToCart(productData);
            }
        });

        // Discount Modal Actions
        document.getElementById('apply-fixed-discount-btn').addEventListener('click', function() {
            const fixedDiscount = parseFloat(document.getElementById('fixed-discount-input').value);
            if (!isNaN(fixedDiscount) && fixedDiscount > 0) {
                discount = { type: 'fixed_amount', value: fixedDiscount, code: 'MANUAL' };
                renderCart();
                $('#discountModal').modal('hide');
            } else {
                alert('Please enter a valid fixed discount amount.');
            }
        });

        document.getElementById('clear-discount-btn').addEventListener('click', function() {
            discount = { type: 'none', value: 0, code: null };
            document.getElementById('fixed-discount-input').value = '';
            document.getElementById('promo-code-input').value = '';
            renderCart();
        });

        // Checkout Modal Logic
        const amountTenderedInput = document.getElementById('amount-tendered');
        const checkoutSummaryTendered = document.getElementById('checkout-summary-tendered');
        const checkoutSummaryChange = document.getElementById('checkout-summary-change');
        const paymentMethodSelect = document.getElementById('payment-method-select');

        function updateCheckoutSummary() {
            const { finalAmount } = calculateTotals();
            const tendered = parseFloat(amountTenderedInput.value) || 0;
            const change = tendered > finalAmount ? tendered - finalAmount : 0;
            
            checkoutSummaryTendered.textContent = formatCurrency(tendered);
            checkoutSummaryChange.textContent = formatCurrency(change);
            
            document.getElementById('process-payment-btn').disabled = tendered < finalAmount;

            // Toggle input visibility based on payment type
            const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
            const type = selectedOption.getAttribute('data-type');

            document.getElementById('cash-input-group').style.display = type === 'cash' ? 'block' : 'none';
            document.getElementById('quick-cash-buttons').style.display = type === 'cash' ? 'flex' : 'none';
            document.getElementById('card-details-group').style.display = type === 'card' ? 'block' : 'none';
            document.getElementById('digital-wallet-group').style.display = type === 'digital_wallet' ? 'block' : 'none';
        }
        
        $('#checkoutModal').on('show.bs.modal', function () {
            const { finalAmount } = calculateTotals();
            document.getElementById('checkout-summary-total').textContent = formatCurrency(finalAmount);
            // Default tender amount to the final amount for non-cash payments, or 0 for cash
            const type = paymentMethodSelect.options[paymentMethodSelect.selectedIndex].getAttribute('data-type');
            amountTenderedInput.value = type !== 'cash' ? finalAmount.toFixed(2) : finalAmount.toFixed(2);
            updateCheckoutSummary();
        });

        amountTenderedInput.addEventListener('input', updateCheckoutSummary);
        paymentMethodSelect.addEventListener('change', updateCheckoutSummary);

        // Quick Tender Buttons
        document.querySelectorAll('.quick-tender').forEach(btn => {
            btn.addEventListener('click', function() {
                const { finalAmount } = calculateTotals();
                let amount = parseFloat(this.getAttribute('data-amount'));

                if (this.id === 'quick-exact') {
                    amount = finalAmount;
                } else if (this.id === 'quick-custom') {
                    // This is a placeholder for manual entry, which the input handles
                    return;
                }
                
                if (!isNaN(amount) && amount > 0) {
                    amountTenderedInput.value = amount.toFixed(2);
                }
                
                updateCheckoutSummary();
            });
        });

        // Final Checkout Submission
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (cart.length === 0) return;

            const { finalAmount, totalDiscount, taxAmount, subtotal } = calculateTotals();
            const amountTendered = parseFloat(amountTenderedInput.value);
            const changeAmount = amountTendered - finalAmount;

            const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
            const paymentMethodId = selectedOption.value;
            const paymentType = selectedOption.getAttribute('data-type');
            
            let referenceNumber = null;
            if (paymentType === 'card') {
                referenceNumber = document.getElementById('reference-number').value;
            } else if (paymentType === 'digital_wallet') {
                referenceNumber = document.getElementById('wallet-reference').value;
            }

            const checkoutData = {
                cart_items: cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    selling_price: item.price,
                })),
                payments: [{
                    payment_method_id: paymentMethodId,
                    amount_paid: finalAmount, // Assuming single payment method for now
                    amount_tendered: amountTendered,
                    reference_number: referenceNumber,
                }],
                customer_name: document.getElementById('customer-name').value,
                total_discount: totalDiscount,
                promo_code: discount.code,
            };

            fetch('{{ route('pos.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(checkoutData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Transaction Complete! Change: ${formatCurrency(data.change)}. Printing Receipt...`);
                    // Clear state and close modal
                    cart = [];
                    discount = { type: 'none', value: 0, code: null };
                    renderCart();
                    $('#checkoutModal').modal('hide');
                    
                    // Redirect to receipt
                    window.open(`{{ url('/pos/receipt') }}/${data.sale_id}`, '_blank');
                    
                } else {
                    alert('Checkout Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('A network error occurred during checkout.');
            });
        });
        
        // New Transaction Button
        document.getElementById('new-transaction').addEventListener('click', function() {
            if (cart.length > 0 && !confirm('Are you sure you want to cancel the current transaction?')) {
                return;
            }
            cart = [];
            discount = { type: 'none', value: 0, code: null };
            renderCart();
        });

        // Keyboard Shortcuts (F1 for Checkout, F2 for Search/Focus)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F1') {
                e.preventDefault();
                if (cart.length > 0) {
                    $('#checkoutModal').modal('show');
                }
            } else if (e.key === 'F2') {
                e.preventDefault();
                document.getElementById('barcode-input').focus();
            }
        });
        
    });
</script>

<style>
.pos-container { height: 95vh; padding: 0; }
.pos-left-panel, .pos-right-panel { padding: 0 5px; }
.h-100 { height: 100%; }
.card { border: none; }
.card-body { padding: 10px; }
.pos-container .table thead th { position: sticky; top: 0; background-color: #f8f9fa; z-index: 10; }
.product-card { cursor: pointer; border: 1px solid #dee2e6; transition: all 0.2s; }
.product-card:hover { border-color: #007bff; background-color: #e9f5ff; }
</style>
@endsection