<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Payments->value)->group(function () {
    // JSON data endpoint the page fetches client-side (useHttp), kept separate
    // from the `Inertia::render`-only page action.
    Route::get('payments/fetch', [PaymentController::class, 'fetch'])->name('payments.fetch');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
});
