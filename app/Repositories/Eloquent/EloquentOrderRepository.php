<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Collection<int, Order>
     */
    public function forStudent(int $studentId): Collection
    {
        return Order::query()
            ->where('user_id', $studentId)
            ->with(['course', 'payments'])
            ->latest()
            ->get();
    }
}
