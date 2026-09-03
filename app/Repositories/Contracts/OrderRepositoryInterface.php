<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * Order reads for the student area. Inherits the base CRUD and adds the
 * "my payments" lookup.
 */
interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * The given student's orders, newest first, each with its course and
     * payments loaded.
     *
     * @return Collection<int, Order>
     */
    public function forStudent(int $studentId): Collection;
}
