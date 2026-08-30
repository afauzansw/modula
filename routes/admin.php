<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::redirect('admin', '/admin/dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::inertia('dashboard', 'admin/dashboard')
            ->middleware('permission:'.AdminPermission::Dashboard->value)
            ->name('dashboard');

        Route::middleware('permission:'.AdminPermission::Roles->value)->group(function () {
            Route::resource('roles', RoleController::class)->except('show');
        });
    });
