<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Users->value)->group(function () {
    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('instructors', [InstructorController::class, 'index'])->name('instructors.index');
});
