<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class GuardedEnableTwoFactorAuthentication extends EnableTwoFactorAuthentication
{
    public function __invoke($user, $force = false)
    {
        parent::__invoke($user, $force && $user->two_factor_confirmed_at === null);
    }
}
