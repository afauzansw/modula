<?php

namespace App\Repositories\Eloquent;

use App\Models\Admin;
use App\Models\User;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentAdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['name', 'email'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'email', 'created_at'];

    /** @var list<string> */
    protected array $with = ['permissions'];

    public function __construct(Admin $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::callback(
            'permission',
            fn ($query, $value) => $query->whereHas('permissions', fn ($permissions) => $permissions->where('name', $value)),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Admin {
            $admin = Admin::query()->forceCreate([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $admin->syncPermissions($data['permissions'] ?? []);

            return $admin;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data): Model {
            $attributes = ['name' => $data['name'], 'email' => $data['email']];

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $model->update($attributes);

            if ($model instanceof User && array_key_exists('permissions', $data)) {
                $model->syncPermissions($data['permissions']);
            }

            return $model;
        });
    }
}
