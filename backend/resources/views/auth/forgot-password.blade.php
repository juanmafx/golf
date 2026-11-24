@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
<section class="card">
    <h1>Reset password</h1>
    <form class="form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
        <button type="submit">Send reset link</button>
    </form>
</section>
@endsection
