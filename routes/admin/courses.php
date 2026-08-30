<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('courses', [CourseController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Courses->value)
    ->name('courses.index');
