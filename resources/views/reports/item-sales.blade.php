@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Item Sales Report</h1>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="submit" name="download" value="1" class="btn btn-success">
                <i class="bi bi-download"></i> PDF
            </button>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Product Name</th>
                <th>Total Quantity Sold</th>
                <th>Total Revenue</th>
                <th>Average Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemSales as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Product not found' }}</td>
                <td>{{ number_format($item->total_quantity) }}</td>
                <td>₱{{ number_format($item->total_revenue, 2) }}</td>
                <td>₱{{ $item->total_quantity > 0 ? number_format($item->total_revenue / $item->total_quantity, 2) : '0.00' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No sales data found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection