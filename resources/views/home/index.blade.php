@extends('layouts.app-master')

@section('content')
<div class="container py-4">
  <h1 class="mb-4">Welcome, {{ auth()->user()->username ?? 'Guest' }}!</h1>
  <p>You are successfully logged in.</p>
</div>
@endsection