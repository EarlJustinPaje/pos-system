<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->sale_id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
        }
        .header, .footer, .totals, .details {
            text-align: center;
            margin-bottom: 10pt;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10pt;
        }
        .item-table th, .item-table td {
            text-align: left;
            padding: 2pt 0;
        }
        .item-table .qty { width: 10%; text-align: center; }
        .item-table .price { width: 30%; text-align: right; }
        .item-table .total { width: 30%; text-align: right; }
        .hr {
            border-top: 1px dashed #000;
            margin: 5pt 0;
        }
        .total-row strong { float: right; }
        .text-right { text-align: right; }
        @media print {
            body { max-width: none; width: 80mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>{{ config('app.name', 'POS System') }}</h2>
        <p>
            {{ $sale->branch->name ?? 'Head Office' }}<br>
            {{ $sale->branch->address ?? 'No Address' }}<br>
            TIN: XXX-XXX-XXX-XXX (Non-VAT)<br>
            <small>Sales Invoice / Official Receipt</small>
        </p>
    </div>

    <div class="details">
        <p class="text-left">
            <strong>TRX ID:</strong> {{ $sale->sale_id }}<br>
            <strong>Date:</strong> {{ $sale->created_at->format('M d, Y h:i A') }}<br>
            <strong>Cashier:</strong> {{ $sale->user->name ?? 'N/A' }}<br>
            <strong>Customer:</strong> {{ $sale->customer_name ?? 'Walk-in Customer' }}
        </p>
    </div>

    <div class="hr"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Qty</th>
                <th class="price">Price</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="price">{{ number_format($item->selling_price, 2) }}</td>
                    <td class="total">{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="hr"></div>

    <div class="totals">
        <p class="total-row">Subtotal: <strong>{{ number_format($sale->total_amount, 2) }}</strong></p>
        <p class="total-row">Discount: <strong>({{ number_format($sale->discount_amount, 2) }})</strong></p>
        <p class="total-row">Tax ({{ $sale->tax_rate }}%): <strong>{{ number_format($sale->tax_amount, 2) }}</strong></p>
        <p class="total-row"><h3>TOTAL DUE: <strong>₱{{ number_format($sale->final_amount, 2) }}</strong></h3></p>
        <br>
        
        @foreach($sale->paymentTransactions as $transaction)
            <p class="total-row">
                {{ $transaction->paymentMethod->name ?? 'Payment' }} Tendered: 
                <strong>{{ number_format($transaction->amount_tendered, 2) }}</strong>
            </p>
        @endforeach
        
        <p class="total-row">
            Change Due: 
            <strong>₱{{ number_format($sale->change_amount, 2) }}</strong>
        </p>
        
    </div>

    <div class="hr"></div>

    <div class="footer">
        <p>THANK YOU FOR YOUR PURCHASE!</p>
        <p>Please come again.</p>
        <p><small>System Generated. This serves as your Official Receipt.</small></p>
    </div>
</body>
</html>