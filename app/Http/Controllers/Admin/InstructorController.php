<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateUserStatusRequest;
use App\Repositories\Contracts\InstructorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function __construct(private readonly InstructorRepositoryInterface $instructors) {}

    public function index(): Response
    {
        return Inertia::render('admin/instructors/index');
    }

    public function fetch(): JsonResponse
    {
        return response()->json($this->instructors->all());
    }

    public function bulkUpdateStatus(BulkUpdateUserStatusRequest $request): RedirectResponse
    {
        $updated = $this->instructors->bulkUpdate(
            $request->validated('ids'),
            ['is_blocked' => $request->boolean('is_blocked')],
        );

        $verb = $request->boolean('is_blocked') ? 'blocked' : 'unblocked';

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice("{0}No instructors {$verb}.|{1}:count instructor {$verb}.|[2,*]:count instructors {$verb}.", $updated, ['count' => $updated]),
        ]);

        return redirect()->route('admin.instructors.index');
    }
}
