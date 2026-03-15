<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('settings.profile');
});

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::livewire('settings/password', 'pages::settings.password')->name('settings.password');
    Route::livewire('settings/company', 'pages::settings.company')->name('settings.company');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('settings.appearance');

    Route::livewire('settings/two-factor', 'pages::settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('settings.two-factor');
});
