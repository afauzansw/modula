<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct(private readonly RoleRepositoryInterface $roles) {}

    public function index(Request $request): Response
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

        return Inertia::render('admin/roles/index', [
            'roles' => [
                'data' => $data,
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'links' => $this->paginationLinks($roles),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/roles/create', [
            'permissions' => AdminPermission::labels(),
        ]);
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
            'permissions' => AdminPermission::labels(),
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
