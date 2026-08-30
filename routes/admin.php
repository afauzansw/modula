<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;

Route::redirect('admin', '/admin/dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::inertia('dashboard', 'admin/dashboard')
            ->middleware('permission:'.AdminPermission::Dashboard->value)
            ->name('dashboard');

        Route::middleware('permission:'.AdminPermission::Courses->value)->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        });

        Route::middleware('permission:'.AdminPermission::Categories->value)->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        });

        Route::middleware('permission:'.AdminPermission::Payments->value)->group(function () {
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        });

        Route::middleware('permission:'.AdminPermission::Certificates->value)->group(function () {
            Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        });

        Route::middleware('permission:'.AdminPermission::Users->value)->group(function () {
            Route::get('students', [StudentController::class, 'index'])->name('students.index');
            Route::get('instructors', [InstructorController::class, 'index'])->name('instructors.index');
        });

        Route::middleware('permission:'.AdminPermission::Admins->value)->group(function () {
            Route::get('admins', [AdminUserController::class, 'index'])->name('admins.index');
        });

        Route::middleware('permission:'.AdminPermission::Roles->value)->group(function () {
            Route::resource('roles', RoleController::class)->except('show');
        });

        Route::middleware('permission:'.AdminPermission::Settings->value)->group(function () {
            Route::get('settings', [AppSettingController::class, 'index'])->name('settings');
        });
    });
