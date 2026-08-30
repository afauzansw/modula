<?php

use Illuminate\Support\Facades\Route;

Route::redirect('instructor', '/instructor/dashboard');

Route::middleware(['auth', 'verified', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::inertia('dashboard', 'instructor/dashboard')->name('dashboard');
    });
