<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Roles->value)->group(function () {
    // Registered before the resource routes so `roles/bulk-destroy` isn't
    // swallowed by `roles/{role}`.
    Route::delete('roles/bulk-destroy', [RoleController::class, 'bulkDestroy'])
        ->name('roles.bulk-destroy');

    Route::resource('roles', RoleController::class)->except('show');
});
