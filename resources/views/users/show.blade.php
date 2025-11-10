@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>User Profile</h5>
                <div>
                    @if($user->id === auth()->id() || auth()->user()->is_admin)
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">Edit Profile</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Name:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $user->name ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Username:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $user->username }}
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $user->email }}
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-9">
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Role:</strong>
                    </div>
                    <div class="col-md-9">
                        <span class="badge {{ $user->is_admin ? 'bg-primary' : 'bg-secondary' }}">
                            {{ $user->is_admin ? 'Administrator' : 'User' }}
                        </span>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Member Since:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $user->created_at->format('F j, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection