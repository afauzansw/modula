<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Roles->value)->group(function () {
    // Registered before the resource routes so these fixed paths aren't
    // swallowed by `roles/{role}`.
    Route::delete('roles/bulk-destroy', [RoleController::class, 'bulkDestroy'])
        ->name('roles.bulk-destroy');

    // JSON data endpoints the pages fetch client-side (useHttp), kept separate
    // from the `Inertia::render`-only page actions.
    Route::get('roles/fetch', [RoleController::class, 'fetch'])->name('roles.fetch');
    Route::get('roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');

    Route::resource('roles', RoleController::class)->except('show');
});
