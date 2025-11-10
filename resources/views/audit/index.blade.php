@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Audit Trail</h1>
    
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        <select name="action" class="form-control">
            <option value="">All Actions</option>
            <option value="CREATE" {{ request('action') == 'CREATE' ? 'selected' : '' }}>Create</option>
            <option value="UPDATE" {{ request('action') == 'UPDATE' ? 'selected' : '' }}>Update</option>
            <option value="DELETE" {{ request('action') == 'DELETE' ? 'selected' : '' }}>Delete</option>
            <option value="DEACTIVATE" {{ request('action') == 'DEACTIVATE' ? 'selected' : '' }}>Deactivate</option>
            <option value="PASSWORD_RESET" {{ request('action') == 'PASSWORD_RESET' ? 'selected' : '' }}>Password Reset</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead class="table-dark">
            <tr>
                <th>Date/Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Table</th>
                <th>Record ID</th>
                <th>IP Address</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->user->name ?? $log->user->username ?? 'System' }}</td>
                <td>
                    <span class="badge 
                        @if($log->action == 'CREATE') bg-success
                        @elseif($log->action == 'UPDATE') bg-warning
                        @elseif($log->action == 'DELETE') bg-danger
                        @elseif($log->action == 'DEACTIVATE') bg-secondary
                        @else bg-info
                        @endif">
                        {{ $log->action }}
                    </span>
                </td>
                <td>{{ $log->table_name }}</td>
                <td>{{ $log->record_id }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>
                    @if($log->old_values || $log->new_values)
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                data-bs-target="#detailModal{{ $log->id }}">
                            <i class="bi bi-eye"></i>
                        </button>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Audit Log Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($log->old_values)
                                            <h6>Old Values:</h6>
                                            <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                        
                                        @if($log->new_values)
                                            <h6>New Values:</h6>
                                            <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No audit logs found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center">
    {{ $logs->withQueryString()->links() }}
</div>
@endsection