<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDestroyRoleRequest;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct(private readonly RoleRepositoryInterface $roles) {}

    public function index(): Response
    {
        return Inertia::render('admin/roles/index');
    }

    /**
     * The paginated role listing the DataTable pulls from — one
     * {id,name,is_system,permissions} row per role plus the paginator meta.
     * Driven by the request's `filter` / `sort` / `page` query params through
     * the repository's query builder.
     */
    public function fetch(Request $request): JsonResponse
    {
        $request->merge(['include' => 'permissions']);

        $roles = $this->roles->all();

        $data = [];

        foreach ($roles->items() as $role) {
            if (! $role instanceof Role) {
                continue;
            }

            $data[] = [
                'id' => $role->id,
                'name' => $role->name,
                'is_system' => $role->is_system,
                'permissions' => $role->permissions->pluck('name')->all(),
            ];
        }

        return response()->json([
            'data' => $data,
            'current_page' => $roles->currentPage(),
            'last_page' => $roles->lastPage(),
            'per_page' => $roles->perPage(),
            'total' => $roles->total(),
            'links' => $this->paginationLinks($roles),
        ]);
    }

    /**
     * The admin-permission catalogue (name => label) the role form renders its
     * checkboxes from. Code-defined; see {@see AdminPermission}.
     */
    public function permissions(): JsonResponse
    {
        return response()->json(AdminPermission::labels());
    }

    public function create(): Response
    {
        return Inertia::render('admin/roles/create');
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->createCustomRole($request->string('name')->toString(), $request->input('permissions', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role created.')]);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role): Response
    {
        abort_if($role->is_system, 403);

        return Inertia::render('admin/roles/edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
            ],
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        $this->roles->updateCustomRole($role, $request->string('name')->toString(), $request->input('permissions', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        $this->roles->deleteCustomRole($role);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role deleted.')]);

        return redirect()->route('admin.roles.index');
    }

    /**
     * Delete every selected custom role in one query. System roles among the
     * ids are silently skipped (EloquentRoleRepository::bulkDelete).
     */
    public function bulkDestroy(BulkDestroyRoleRequest $request): RedirectResponse
    {
        $deleted = $this->roles->bulkDelete($request->validated('ids'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice('{0}No roles deleted.|{1}:count role deleted.|[2,*]:count roles deleted.', $deleted, ['count' => $deleted]),
        ]);

        return redirect()->route('admin.roles.index');
    }

    /**
     * Build the {url,label,active} link list the frontend paginator expects,
     * using only the paginator contract's declared methods.
     *
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @return list<array{url: ?string, label: string, active: bool}>
     */
    private function paginationLinks(LengthAwarePaginator $paginator): array
    {
        $links = [
            ['url' => $paginator->previousPageUrl(), 'label' => '&laquo; Previous', 'active' => false],
        ];

        foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url) {
            $links[] = ['url' => $url, 'label' => (string) $page, 'active' => $page === $paginator->currentPage()];
        }

        $links[] = ['url' => $paginator->nextPageUrl(), 'label' => 'Next &raquo;', 'active' => false];

        return $links;
    }
}
