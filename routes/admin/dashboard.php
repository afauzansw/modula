<?php

use App\Enums\AdminPermission;
use Illuminate\Support\Facades\Route;

Route::inertia('dashboard', 'admin/dashboard')
    ->middleware('permission:'.AdminPermission::Dashboard->value)
    ->name('dashboard');
