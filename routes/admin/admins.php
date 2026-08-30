<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('admins', [AdminUserController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Admins->value)
    ->name('admins.index');
