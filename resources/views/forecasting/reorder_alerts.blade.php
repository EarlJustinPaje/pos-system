@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Inventory Reorder Alerts</h1>

    <div class="alert alert-info">
        <p>Reorder Alerts are triggered when a product's current stock falls below its defined Reorder Point. This is a crucial step to prevent stockouts.</p>
        <form action="{{ route('forecasting.generate-alerts') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark">Re-check Inventory & Generate New Alerts</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Alert ID</th>
                            <th>Product Name</th>
                            <th>Branch</th>
                            <th>Current Stock</th>
                            <th>Reorder Point</th>
                            <th>Recommended Qty</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alerts as $alert)
                            <tr>
                                <td>{{ $alert->id }}</td>
                                <td><a href="{{ route('products.show', $alert->product_id) }}">{{ $alert->product->name ?? 'N/A' }}</a></td>
                                <td>{{ $alert->branch->name ?? 'N/A' }}</td>
                                <td>{{ $alert->current_quantity }}</td>
                                <td>{{ $alert->reorder_point }}</td>
                                <td>{{ $alert->recommended_quantity }}</td>
                                <td>
                                    <span class="badge badge-{{ $alert->getPriorityBadgeClass() }}">{{ ucfirst($alert->priority) }}</span>
                                </td>
                                <td><span class="badge {{ $alert->getStatusBadgeClass() }}">{{ ucfirst($alert->status) }}</span></td>
                                <td>
                                    @if($alert->status === 'pending' || $alert->status === 'acknowledged')
                                        <form action="{{ route('forecasting.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Acknowledge">
                                                {{ $alert->status === 'pending' ? 'Acknowledge' : 'Re-Acknowledge' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('forecasting.alerts.ordered', $alert) }}" method="POST" class="d-inline ml-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as Ordered">
                                                Mark Ordered
                                            </button>
                                        </form>
                                    @endif
                                    @if($alert->status !== 'resolved')
                                        <form action="{{ route('forecasting.alerts.resolve', $alert) }}" method="POST" class="d-inline ml-1" onsubmit="return confirm('Only resolve if stock is restored.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success" title="Resolve Alert">
                                                Resolve
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No pending reorder alerts found. Your stock levels are healthy!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $alerts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection