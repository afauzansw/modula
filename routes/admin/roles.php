<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class)
    ->except('show')
    ->middleware('permission:'.AdminPermission::Roles->value);
