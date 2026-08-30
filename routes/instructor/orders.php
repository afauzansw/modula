<?php

use App\Http\Controllers\Instructor\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
