@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<section class="card">
    <h1>Log in</h1>
    <form class="form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
        <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
        <label class="checkbox"><input type="checkbox" name="remember"> Remember me</label>
        <button type="submit">Log in</button>
    </form>
    <p><a href="{{ route('password.request') }}">Forgot your password?</a></p>
</section>
@endsection
