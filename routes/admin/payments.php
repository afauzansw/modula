<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('payments', [PaymentController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Payments->value)
    ->name('payments.index');
