@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1>Import Log #{{ $import->id }}</h1>
    
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge {{ $import->getStatusBadgeClass() }}">{{ $import->status }}</span></p>
                    <p><strong>Initiated By:</strong> {{ $import->user->name ?? 'System' }}</p>
                    <p><strong>Target Branch:</strong> {{ $import->branch->name ?? 'N/A' }}</p>
                    <p><strong>Start Time:</strong> {{ $import->created_at->format('M d, Y H:i:s') }}</p>
                    @if($import->completed_at)
                        <p><strong>End Time:</strong> {{ $import->completed_at->format('M d, Y H:i:s') }}</p>
                        <p><strong>Duration:</strong> {{ $import->completed_at->diffForHumans($import->created_at, true) }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>Total Rows:</strong> {{ $import->total_rows }}</p>
                    <p><strong>Processed Rows:</strong> {{ $import->processed_rows }}</p>
                    <p><strong>Successful Imports:</strong> <span class="badge badge-success">{{ $import->successful_rows }}</span></p>
                    <p><strong>Failed Rows:</strong> <span class="badge badge-danger">{{ $import->failed_rows }}</span></p>
                </div>
            </div>
            
            <div class="progress mt-3">
                <div class="progress-bar progress-bar-striped {{ $import->getProgressBarClass() }}" 
                     role="progressbar" 
                     style="width: {{ $import->getPercentageComplete() }}%" 
                     aria-valuenow="{{ $import->processed_rows }}" 
                     aria-valuemin="0" 
                     aria-valuemax="{{ $import->total_rows }}">
                    {{ $import->getPercentageComplete() }}%
                </div>
            </div>
            
            @if($import->status === 'processing')
                <p class="text-center mt-2">Processing... Refresh this page to check progress.</p>
            @endif
        </div>
    </div>

    @if(!empty($import->errors) || $import->status === 'failed')
        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                Error Details
            </div>
            <div class="card-body">
                @if($import->status === 'failed' && isset($import->errors['general_error']))
                    <p><strong>General Error:</strong> {{ $import->errors['general_error'] }}</p>
                    <hr>
                @endif
                
                @if(is_array($import->errors) && count($import->errors) > 0)
                    <h4>Row-Specific Errors ({{ count($import->errors) }} errors)</h4>
                    <ul class="list-group">
                        @foreach($import->errors as $rowNumber => $error)
                            @if(is_numeric($rowNumber))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Row {{ $rowNumber }}: 
                                    <span class="text-danger">{{ $error }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                
                @if($import->status === 'failed')
                    <a href="{{ route('products.imports.retry', $import) }}" class="btn btn-secondary mt-3">Retry Import</a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection