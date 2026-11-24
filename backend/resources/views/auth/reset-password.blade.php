@extends('layouts.app')

@section('title', 'Choose a new password')

@section('content')
<section class="card">
    <h1>Choose a new password</h1>
    <form class="form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <label>Email <input type="email" name="email" value="{{ old('email', $request->query('email')) }}" autocomplete="username" required></label>
        <label>New password <input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="128" required autofocus></label>
        <label>Confirm password <input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="128" required></label>
        <button type="submit">Update password</button>
    </form>
</section>
@endsection
