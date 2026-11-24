<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecuritySettingsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $hasSecret = $user->two_factor_secret !== null;
        $isConfirmed = $user->two_factor_confirmed_at !== null;

        return view('account.security', [
            'isPending' => $hasSecret && ! $isConfirmed,
            'isConfirmed' => $hasSecret && $isConfirmed,
            'qrCodeSvg' => $hasSecret && ! $isConfirmed ? $user->twoFactorQrCodeSvg() : null,
            'recoveryCodes' => $hasSecret && $isConfirmed ? $user->recoveryCodes() : [],
        ]);
    }
}
