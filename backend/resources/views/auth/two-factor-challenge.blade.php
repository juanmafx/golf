@extends('layouts.app')

@section('title', 'Two-factor authentication')

@section('content')
<section class="card">
    <h1>Two-factor authentication</h1>
    <form class="form" method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <label>Authentication code <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" autofocus></label>
        <button type="submit">Verify code</button>
    </form>
    <form class="form" method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <label>Recovery code <input type="text" name="recovery_code" autocomplete="off"></label>
        <button type="submit">Use recovery code</button>
    </form>
</section>
@endsection
