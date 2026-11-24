@extends('layouts.app')

@section('title', 'Register')

@section('content')
<section class="card">
    <h1>Register</h1>
    <form class="form" method="POST" action="{{ route('register.store') }}">
        @csrf
        <label>Name <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus></label>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" maxlength="255" required></label>
        <label>Password <input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="128" required></label>
        <label>Confirm password <input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="128" required></label>
        <button type="submit">Create account</button>
    </form>
</section>
@endsection
