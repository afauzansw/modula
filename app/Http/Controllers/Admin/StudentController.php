<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateUserStatusRequest;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function __construct(private readonly StudentRepositoryInterface $students) {}

    public function index(): Response
    {
        return Inertia::render('admin/students/index');
    }

    public function fetch(): JsonResponse
    {
        return response()->json($this->students->all());
    }

    public function bulkUpdateStatus(BulkUpdateUserStatusRequest $request): RedirectResponse
    {
        $updated = $this->students->bulkUpdate(
            $request->validated('ids'),
            ['is_blocked' => $request->boolean('is_blocked')],
        );

        $verb = $request->boolean('is_blocked') ? 'blocked' : 'unblocked';

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice("{0}No students {$verb}.|{1}:count student {$verb}.|[2,*]:count students {$verb}.", $updated, ['count' => $updated]),
        ]);

        return redirect()->route('admin.students.index');
    }
}
