<?php

use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\OrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('instructor', '/instructor/dashboard');

Route::middleware(['auth', 'verified', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::inertia('dashboard', 'instructor/dashboard')->name('dashboard');

        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    });
