<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserRequest;
use App\Http\Requests\Admin\BulkDestroyAdminRequest;
use App\Models\Admin;
use App\Models\User;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminRepositoryInterface $admins) {}

    public function index(): Response
    {
        return Inertia::render('admin/admins/index');
    }

    public function fetch(): JsonResponse
    {
        $admins = $this->admins->all();

        $rows = [];

        foreach ($admins->items() as $admin) {
            if (! $admin instanceof User) {
                continue;
            }

            $rows[] = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'permissions' => $admin->permissions->pluck('name')->all(),
                'created_at' => $admin->created_at?->toIso8601String(),
            ];
        }

        return $this->paginatedJson($admins, $rows);
    }

    public function store(AdminUserRequest $request): RedirectResponse
    {
        $this->admins->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin created.')]);

        return redirect()->route('admin.admins.index');
    }

    public function update(AdminUserRequest $request, Admin $admin): RedirectResponse
    {
        $this->admins->update($admin, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin updated.')]);

        return redirect()->route('admin.admins.index');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        abort_if($admin->getKey() === Auth::id(), 403);

        $this->admins->delete($admin);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin deleted.')]);

        return redirect()->route('admin.admins.index');
    }

    public function bulkDestroy(BulkDestroyAdminRequest $request): RedirectResponse
    {
        $ids = array_values(array_diff($request->validated('ids'), array_filter([Auth::id()])));

        $deleted = $this->admins->bulkDelete($ids);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice('{0}No admins deleted.|{1}:count admin deleted.|[2,*]:count admins deleted.', $deleted, ['count' => $deleted]),
        ]);

        return redirect()->route('admin.admins.index');
    }
}
