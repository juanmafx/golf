@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="card">
    <h1>Dashboard</h1>
    <p>You are logged in to {{ config('app.name') }}.</p>
    <p><a href="{{ route('account.security') }}">Manage two-factor authentication</a></p>
</section>
@endsection
