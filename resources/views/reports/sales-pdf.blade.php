<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sales Report</h2>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
        <p><strong>Total Sales: ₱{{ number_format($totalSales, 2) }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Total Amount</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $sale->user->name ?? $sale->user->username }}</td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td>
                    @foreach($sale->saleItems as $item)
                        {{ $item->product->name }} ({{ $item->quantity }})<br>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>