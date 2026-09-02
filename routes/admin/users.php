<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Users->value)->group(function () {
    // Fixed paths before the index routes; JSON data endpoints fetched
    // client-side (useHttp), separate from the `Inertia::render`-only pages.
    Route::patch('students/status', [StudentController::class, 'bulkUpdateStatus'])
        ->name('students.bulk-update-status');
    Route::get('students/fetch', [StudentController::class, 'fetch'])->name('students.fetch');
    Route::get('students', [StudentController::class, 'index'])->name('students.index');

    Route::patch('instructors/status', [InstructorController::class, 'bulkUpdateStatus'])
        ->name('instructors.bulk-update-status');
    Route::get('instructors/fetch', [InstructorController::class, 'fetch'])->name('instructors.fetch');
    Route::get('instructors', [InstructorController::class, 'index'])->name('instructors.index');
});
