<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CertificateController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:'.AdminPermission::Certificates->value)->group(function () {
    // JSON data endpoint the page fetches client-side (useHttp), separate from
    // the `Inertia::render`-only page action.
    Route::get('certificates/fetch', [CertificateController::class, 'fetch'])->name('certificates.fetch');

    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
});
