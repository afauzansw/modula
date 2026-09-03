<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Admins->value)->group(function () {
    // Fixed paths before the resource routes so `admins/{admin}` doesn't swallow them.
    Route::delete('admins/bulk-destroy', [AdminUserController::class, 'bulkDestroy'])
        ->name('admins.bulk-destroy');

    Route::get('admins/fetch', [AdminUserController::class, 'fetch'])->name('admins.fetch');

    // create/edit are modals on the index page, not standalone pages.
    Route::resource('admins', AdminUserController::class)->except(['show', 'create', 'edit']);
});
