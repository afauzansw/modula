<?php

use App\Http\Controllers\Student\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
