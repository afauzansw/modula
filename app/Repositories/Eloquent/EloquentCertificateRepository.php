<?php

namespace App\Repositories\Eloquent;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentCertificateRepository extends BaseRepository implements CertificateRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['certificate_number'];

    /** @var list<string> */
    protected array $allowedSorts = ['issued_at', 'created_at'];

    /** @var list<string> */
    protected array $with = ['user', 'course'];

    public function __construct(Certificate $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::exact('user_id');
        $this->allowedFilters[] = AllowedFilter::exact('course_id');
    }
}
