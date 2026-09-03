<?php

use App\Http\Controllers\Instructor\CourseController;
use Illuminate\Support\Facades\Route;

// Fixed paths before the resource routes so `courses/{course}` doesn't swallow them.
Route::patch('courses/status', [CourseController::class, 'bulkUpdateStatus'])->name('courses.bulk-update-status');
Route::delete('courses/bulk-destroy', [CourseController::class, 'bulkDestroy'])->name('courses.bulk-destroy');

// JSON data endpoints the index fetches client-side (useHttp).
Route::get('courses/fetch', [CourseController::class, 'fetch'])->name('courses.fetch');
Route::get('courses/categories', [CourseController::class, 'categories'])->name('courses.categories');

// create / edit are full pages here (not modals) — keep them on the resource.
Route::resource('courses', CourseController::class)->except(['show']);
