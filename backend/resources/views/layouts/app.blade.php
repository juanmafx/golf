<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>
    @vite('src/main.ts')
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('welcome') }}">{{ config('app.name') }}</a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-controls="site-nav" aria-expanded="false">Menu</button>
        <nav id="site-nav" class="site-nav" data-nav aria-label="Primary">
            @auth
                <a href="{{ route('home') }}">Dashboard</a>
                <a href="{{ route('account.security') }}">Security</a>
                <span class="nav-user">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="link-button" type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>
    <main class="page">
        @if (session('status'))
            <p class="alert" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <ul class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        @yield('content')
    </main>
</body>
</html>
