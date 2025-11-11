@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Product Bulk Imports</h1>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p>Use this page to track the status of your bulk product imports. Imports run in the background.</p>
                    <a href="{{ route('products.imports.download-template') }}" class="btn btn-sm btn-info">Download Import Template (.xlsx)</a>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('products.imports.create') }}" class="btn btn-primary">Start New Import</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Branch</th>
                            <th>Total Rows</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Errors</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($imports as $import)
                            <tr>
                                <td>{{ $import->id }}</td>
                                <td>{{ $import->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $import->user->name ?? 'System' }}</td>
                                <td>{{ $import->branch->name ?? 'N/A' }}</td>
                                <td>{{ $import->total_rows }}</td>
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar progress-bar-striped {{ $import->getProgressBarClass() }}" 
                                             role="progressbar" 
                                             style="width: {{ $import->getPercentageComplete() }}%" 
                                             aria-valuenow="{{ $import->processed_rows }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $import->total_rows }}">
                                            {{ $import->getPercentageComplete() }}%
                                        </div>
                                    </div>
                                    <small>{{ $import->processed_rows }} / {{ $import->total_rows }} processed</small>
                                </td>
                                <td><span class="badge {{ $import->getStatusBadgeClass() }}">{{ $import->status }}</span></td>
                                <td>
                                    @if($import->failed_rows > 0)
                                        <span class="badge badge-danger">{{ $import->failed_rows }} Failed</span>
                                    @elseif($import->status === 'failed')
                                        <span class="badge badge-danger">General Error</span>
                                    @else
                                        <span class="badge badge-success">{{ $import->successful_rows }} Success</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('products.imports.show', $import) }}" class="btn btn-sm btn-info">View Log</a>
                                    @if($import->status === 'failed')
                                        <a href="{{ route('products.imports.retry', $import) }}" class="btn btn-sm btn-secondary">Retry</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No import history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $imports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection