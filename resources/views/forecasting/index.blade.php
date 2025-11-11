@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Demand Forecasting</h1>

    <div class="alert alert-warning">
        <p>This forecast is based on a **basic 90-day average model**. For more accurate predictions, consider incorporating seasonality and external factors.</p>
        <form action="{{ route('forecasting.generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark">Run New Forecast (30 days)</button>
        </form>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('forecasting.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by product name or SKU..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 text-right">
                        <button class="btn btn-primary" type="submit">Filter Forecasts</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th>Forecasted Demand (30 Days)</th>
                            <th>Recommended Order</th>
                            <th>Forecast Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forecasts as $forecast)
                            <tr>
                                <td>{{ $forecast->product->name ?? 'N/A' }}</td>
                                <td>{{ $forecast->product->sku ?? 'N/A' }}</td>
                                <td>{{ $forecast->current_stock }} {{ $forecast->product->unit ?? '' }}</td>
                                <td><span class="badge badge-primary">{{ $forecast->predicted_demand }}</span></td>
                                <td>
                                    @if($forecast->recommended_order_quantity > 0)
                                        <span class="badge badge-warning">{{ $forecast->recommended_order_quantity }} units</span>
                                    @else
                                        <span class="badge badge-success">OK</span>
                                    @endif
                                </td>
                                <td>{{ $forecast->forecast_date->format('M d, Y') }}</td>
                                <td>
                                    @if($forecast->product)
                                        <a href="{{ route('forecasting.show', $forecast->product) }}" class="btn btn-sm btn-info">View Details</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No recent forecasts found. Run the "New Forecast" button to generate initial data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $forecasts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection