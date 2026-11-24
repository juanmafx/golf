@extends('layouts.app')

@section('title', 'Security')

@section('content')
<section class="card">
    <h1>Two-factor authentication</h1>
    @if ($isConfirmed)
        <p>Two-factor authentication is enabled.</p>
        <h2>Recovery codes</h2>
        <p>Store these codes offline. Each code works once.</p>
        <ul class="codes">
            @foreach ($recoveryCodes as $recoveryCode)
                <li><code>{{ $recoveryCode }}</code></li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
            @csrf
            <button type="submit">Regenerate recovery codes</button>
        </form>
        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button class="danger" type="submit">Disable two-factor authentication</button>
        </form>
    @elseif ($isPending)
        <p>Scan this QR code with an authenticator app, then enter the current code.</p>
        <div class="qr-code">{!! $qrCodeSvg !!}</div>
        <form class="form" method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <label>Authentication code <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus></label>
            <button type="submit">Confirm</button>
        </form>
        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button class="link-button" type="submit">Cancel setup</button>
        </form>
    @else
        <p>Add a time-based one-time password from an authenticator app to your sign-in.</p>
        <form method="POST" action="{{ route('two-factor.enable') }}">
            @csrf
            <button type="submit">Enable two-factor authentication</button>
        </form>
    @endif
</section>
@endsection
