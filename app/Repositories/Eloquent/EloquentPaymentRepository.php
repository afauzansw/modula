<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentPaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = [];

    /** @var list<string> */
    protected array $allowedSorts = ['amount', 'paid_at', 'created_at'];

    /** @var list<string> */
    protected array $with = ['order.user', 'order.course'];

    public function __construct(Payment $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::partial('student', 'order.user.name');
        $this->allowedFilters[] = AllowedFilter::partial('course', 'order.course.title');
    }
}
