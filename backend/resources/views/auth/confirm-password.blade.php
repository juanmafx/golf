@extends('layouts.app')

@section('title', 'Confirm password')

@section('content')
<section class="card">
    <h1>Confirm your password</h1>
    <form class="form" method="POST" action="{{ route('password.confirm.store') }}">
        @csrf
        <label>Password <input type="password" name="password" autocomplete="current-password" required autofocus></label>
        <button type="submit">Confirm</button>
    </form>
</section>
@endsection
