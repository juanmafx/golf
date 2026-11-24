@extends('layouts.app')

@section('content')
<section class="card">
    <h1>{{ config('app.name') }}</h1>
    <p>A Laravel application providing account registration, sign-in, password reset and TOTP two-factor authentication.</p>
</section>
@endsection
