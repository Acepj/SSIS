<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\DocumentRequestService;
use SSIS\Services\StudentService;

/**
 * Administrator student management endpoints (list, create, view, update,
 * activate/deactivate). Every value is derived from stored records - the
 * browser never supplies identity or ownership.
 */
final class AdminStudentController
{
    private readonly StudentService $students;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?StudentService $students = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->students = $students ?? new StudentService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function index(Request $request): never
    {
        $userId = Authorization::requireAdmin();

        $search = is_string($request->input('search')) ? $request->input('search') : '';
        $program = is_string($request->input('program')) ? $request->input('program') : '';
        $status = is_string($request->input('status')) ? $request->input('status') : '';

        $students = $this->students->directory($search, $program, $status);

        Response::success(['students' => $students]);
    }

    public function store(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $input = $request->all();

        $created = $this->students->createByAdmin($input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'student.create',
            'target_type' => 'student',
            'target_id' => $created['id'],
            'metadata' => json_encode(['student_id' => $created['studentId'] ?? '']),
        ]);

        Response::success(['student' => $created], 'Student account created.', 201);
    }

    public function show(Request $request): never
    {
        $userId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $student = $this->students->findForAdmin((string) $id);
        if ($student === null) {
            Response::error('NOT_FOUND', 'Student not found.', 404);
        }

        Response::success(['student' => $student]);
    }

    public function update(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');
        $input = $request->all();

        $updated = $this->students->updateByAdmin((string) $id, $input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'student.update',
            'target_type' => 'student',
            'target_id' => (string) $id,
        ]);

        Response::success(['student' => $updated], 'Student updated successfully.');
    }

    public function deactivate(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $this->students->setActive((string) $id, false);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'student.deactivate',
            'target_type' => 'student',
            'target_id' => (string) $id,
        ]);

        Response::success(null, 'Student account deactivated.');
    }

    public function activate(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $this->students->setActive((string) $id, true);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'student.activate',
            'target_type' => 'student',
            'target_id' => (string) $id,
        ]);

        Response::success(null, 'Student account activated.');
    }
}
