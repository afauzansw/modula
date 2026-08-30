<?php

use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\PaymentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/instructor.php';
