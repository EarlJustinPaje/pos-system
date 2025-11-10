@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>User Management</h1>
    
    <form method="GET" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search users..." 
               value="{{ request('search') }}">
        <button type="submit" class="btn btn-outline-primary">Search</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name ?? 'N/A' }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $user->is_admin ? 'bg-primary' : 'bg-secondary' }}">
                        {{ $user->is_admin ? 'Admin' : 'User' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.reset-password', $user) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning" 
                                    onclick="return confirm('Reset password for this user?')">
                                <i class="bi bi-key"></i>
                            </button>
                        </form>
                        
                        @if($user->is_active)
                            <form method="POST" action="{{ route('users.deactivate', $user) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Deactivate this user?')">
                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No users found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection