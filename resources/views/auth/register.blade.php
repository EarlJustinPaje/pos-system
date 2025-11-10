@extends('layouts.auth-master')

@section('content')
<form method="post" action="{{ route('register.perform') }}">
    @csrf

    <img class="mb-4" src="{{ asset('images/bootstrap-logo.svg') }}" alt="Logo" width="72" height="57">
    <h1 class="h3 mb-3 fw-normal">Register</h1>

    @include('layouts.partials.messages')

    <div class="form-floating mb-3">
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
        <label>Email address</label>
    </div>

    <div class="form-floating mb-3">
        <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Username" required>
        <label>Username</label>
    </div>

    <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <label>Password</label>
    </div>

    <div class="form-floating mb-3">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
        <label>Confirm Password</label>
    </div>

    <button class="w-100 btn btn-lg btn-primary" type="submit">Register</button>

    <p class="mt-3">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </p>
</form>
@endsection
