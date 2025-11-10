@extends('layouts.auth-master')

@section('content')
<form method="post" action="{{ route('login.perform') }}">
    @csrf

    <img class="mb-4" src="{{ asset('images/bootstrap-logo.svg') }}" alt="Logo" width="72" height="57">
    <h1 class="h3 mb-3 fw-normal">Login</h1>

    @include('layouts.partials.messages')

    <div class="form-floating mb-3">
        <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Username or Email" required autofocus>
        <label>Username or Email</label>
    </div>

    <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <label>Password</label>
    </div>

    <button class="w-100 btn btn-lg btn-primary" type="submit">Login</button>

    <p class="mt-3">
        Don't have an account? <a href="{{ route('register.show') }}">Register</a>
    </p>
</form>
@endsection
