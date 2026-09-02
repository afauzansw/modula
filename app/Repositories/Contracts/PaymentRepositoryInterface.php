<?php

namespace App\Repositories\Contracts;

/**
 * Student-payment listing for the admin dashboard. Read-only — inherits the
 * base listing/CRUD and adds nothing; every row is a settled `Payment` with
 * its order, student, and course eager-loaded.
 */
interface PaymentRepositoryInterface extends BaseRepositoryInterface {}
