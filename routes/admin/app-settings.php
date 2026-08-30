<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\AppSettingController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [AppSettingController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Settings->value)
    ->name('settings');
