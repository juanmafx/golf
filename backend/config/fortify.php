<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'middleware' => ['web', 'throttle:auth-endpoints'],
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/home',
    'prefix' => '',
    'domain' => null,
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],
    'views' => true,
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
];
