<?php

use App\Http\Controllers\Student\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
