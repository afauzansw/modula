<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Admin\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('certificates', [CertificateController::class, 'index'])
    ->middleware('permission:'.AdminPermission::Certificates->value)
    ->name('certificates.index');
