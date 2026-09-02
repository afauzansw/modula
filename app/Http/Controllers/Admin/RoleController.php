<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDestroyRoleRequest;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\LoadQuery;
use App\Repositories\SpatieQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;

class RoleController extends Controller
{
    public function __construct(private readonly RoleRepositoryInterface $roles) {}

    public function index(): Response
    {
        return Inertia::render('admin/roles/index');
    }

    public function fetch(): JsonResponse
    {
        return response()->json($this->roles->all(
            new SpatieQuery(
                filters: ['name', AllowedFilter::callback(
                    'is_system',
                    fn ($query, $value) => $query->where('is_system', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
                )],
                sorts: ['name', 'created_at'],
            ),
            new LoadQuery(
                with: ['permissions:id,name'],
            ),
        ));
    }

    public function permissions(): JsonResponse
    {
        return response()->json(AdminPermission::labels());
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role created.')]);

        return redirect()->route('admin.roles.index');
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        $this->roles->update($role, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        $this->roles->delete($role);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role deleted.')]);

        return redirect()->route('admin.roles.index');
    }

    public function bulkDestroy(BulkDestroyRoleRequest $request): RedirectResponse
    {
        $deleted = $this->roles->bulkDelete($request->validated('ids'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice('{0}No roles deleted.|{1}:count role deleted.|[2,*]:count roles deleted.', $deleted, ['count' => $deleted]),
        ]);

        return redirect()->route('admin.roles.index');
    }
}
