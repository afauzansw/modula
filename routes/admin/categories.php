<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Categories->value)->group(function () {
    // Fixed paths registered before the resource routes so `categories/{category}`
    // doesn't swallow them.
    Route::delete('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])
        ->name('categories.bulk-destroy');

    Route::get('categories/fetch', [CategoryController::class, 'fetch'])->name('categories.fetch');

    // create/edit are modals on the index page, not standalone pages.
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
});
