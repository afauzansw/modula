<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Courses->value)->group(function () {
    // JSON data endpoints the page fetches client-side (useHttp), kept separate
    // from the `Inertia::render`-only page action.
    Route::get('courses/fetch', [CourseController::class, 'fetch'])->name('courses.fetch');
    Route::get('courses/categories', [CourseController::class, 'categories'])->name('courses.categories');

    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
});
