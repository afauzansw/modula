<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('categories', [CategoryController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Categories->value)
    ->name('categories.index');
