<?php

namespace App\Repositories\Contracts;

/**
 * Issued-certificate listing for the admin dashboard. Read-only — inherits the
 * base listing/CRUD and adds nothing; every row carries its student and course.
 */
interface CertificateRepositoryInterface extends BaseRepositoryInterface {}
