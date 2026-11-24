<?php

use App\Http\Controllers\Account\SecuritySettingsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::middleware('auth')->group(function (): void {
    Route::get('/home', DashboardController::class)->name('home');
    Route::get('/account/security', SecuritySettingsController::class)
        ->middleware('password.confirm')
        ->name('account.security');
});
