@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Sales Report</h1>
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

<div class="card mb-4">
    <div class="card-body">
        <h5>Total Sales: ₱{{ number_format($totalSales, 2) }}</h5>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Sale ID</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Total Amount</th>
                <th>Cash Received</th>
                <th>Change</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $sale->user->name ?? $sale->user->username }}</td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td>₱{{ number_format($sale->cash_received, 2) }}</td>
                <td>₱{{ number_format($sale->change_amount, 2) }}</td>
                <td>
                    <ul class="list-unstyled mb-0">
                        @foreach($sale->saleItems as $item)
                        <li><small>{{ $item->product->name }} ({{ $item->quantity }})</small></li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No sales found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection