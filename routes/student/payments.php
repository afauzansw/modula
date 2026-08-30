<?php

use App\Http\Controllers\Student\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
