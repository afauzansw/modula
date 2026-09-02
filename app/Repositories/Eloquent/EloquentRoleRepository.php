<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentRoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected array $allowedFilters = ['name'];

    protected array $allowedSorts = ['name', 'created_at'];

    protected array $with = ['permissions'];

    public function __construct(Role $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::callback(
            'is_system',
            fn ($query, $value) => $query->where('is_system', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
        );
    }

    protected function afterCreate(Model $model, array $data): void
    {
        if ($model instanceof Role) {
            $model->syncPermissions($data['permissions'] ?? []);
        }
    }

    protected function afterUpdate(Model $model, array $data): void
    {
        if ($model instanceof Role && array_key_exists('permissions', $data)) {
            $model->syncPermissions($data['permissions'] ?? []);
        }
    }
}
