<!DOCTYPE html>
<html>
<head>
    <title>Item Sales Report</title>
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
        <h2>Item Sales Report</h2>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Total Quantity Sold</th>
                <th>Total Revenue</th>
                <th>Average Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemSales as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Product not found' }}</td>
                <td>{{ number_format($item->total_quantity) }}</td>
                <td>₱{{ number_format($item->total_revenue, 2) }}</td>
                <td>₱{{ $item->total_quantity > 0 ? number_format($item->total_revenue / $item->total_quantity, 2) : '0.00' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>