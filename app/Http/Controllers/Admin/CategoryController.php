<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDestroyCategoryRequest;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    public function index(): Response
    {
        return Inertia::render('admin/categories/index');
    }

    public function fetch(): JsonResponse
    {
        return response()->json($this->categories->all());
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return redirect()->route('admin.categories.index');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categories->delete($category);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return redirect()->route('admin.categories.index');
    }

    public function bulkDestroy(BulkDestroyCategoryRequest $request): RedirectResponse
    {
        $deleted = $this->categories->bulkDelete($request->validated('ids'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice('{0}No categories deleted.|{1}:count category deleted.|[2,*]:count categories deleted.', $deleted, ['count' => $deleted]),
        ]);

        return redirect()->route('admin.categories.index');
    }
}
