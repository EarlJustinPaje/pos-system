@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Forecast Details for: {{ $product->name }}</h1>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-light mb-3">
                <div class="card-header">Current Inventory</div>
                <div class="card-body">
                    <h2>{{ $product->quantity }} <small>{{ $product->unit }}</small></h2>
                    <p>Reorder Point: {{ $product->reorder_point }}</p>
                    <p>Reorder Quantity: {{ $product->reorder_quantity }}</p>
                </div>
            </div>
        </div>
        
        @if($latestForecast)
            <div class="col-md-4">
                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary text-white">Latest Demand Prediction (Next 30 Days)</div>
                    <div class="card-body">
                        <h3>{{ $latestForecast->predicted_demand }} <small>units</small></h3>
                        <p>Forecast generated: {{ $latestForecast->created_at->format('M d, Y') }}</p>
                        <p class="text-muted">{{ $latestForecast->notes }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3 {{ $latestForecast->recommended_order_quantity > 0 ? 'border-warning' : 'border-success' }}">
                    <div class="card-header {{ $latestForecast->recommended_order_quantity > 0 ? 'bg-warning' : 'bg-success' }} text-white">Recommended Action</div>
                    <div class="card-body">
                        @if($latestForecast->recommended_order_quantity > 0)
                            <h3>Order {{ $latestForecast->recommended_order_quantity }} units</h3>
                            <p class="text-danger">Reason: Predicted demand exceeds current stock by {{ $latestForecast->predicted_demand - $latestForecast->current_stock }} units, plus buffer.</p>
                        @else
                            <h3>Stock is Sufficient</h3>
                            <p class="text-success">Current stock covers the next 30 days of predicted demand.</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-8">
                <div class="alert alert-info">
                    No forecast data available for this product yet. Run a general forecast from the main page.
                </div>
            </div>
        @endif
    </div>

    @if($latestForecast)
        <div class="card mt-4">
            <div class="card-header">Historical Sales Data (Last 90 Days)</div>
            <div class="card-body">
                <p class="text-muted">A sales trend line chart would normally be displayed here based on the <code>historical_data</code> in the forecast record.</p>
                <p><strong>Raw Data Snippet:</strong></p>
                <pre style="max-height: 200px; overflow-y: scroll;">{{ print_r(json_decode($latestForecast->historical_data, true), true) }}</pre>
            </div>
        </div>
    @endif
</div>
@endsection