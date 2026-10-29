<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Exceptions\AuthorizationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\EnrollmentService;

/**
 * Administrator enrollment management: list, create/update enrollment records,
 * update status using predefined transitions.
 */
final class AdminEnrollmentController
{
    private readonly EnrollmentService $enrollments;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?EnrollmentService $enrollments = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->enrollments = $enrollments ?? new EnrollmentService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function index(Request $request): never
    {
        Authorization::requireAdmin();

        $search = is_string($request->input('search')) ? $request->input('search') : '';
        $program = is_string($request->input('program')) ? $request->input('program') : '';
        $yearLevel = is_string($request->input('yearLevel')) ? $request->input('yearLevel') : '';
        $semester = is_string($request->input('semester')) ? $request->input('semester') : '';
        $academicYear = is_string($request->input('academicYear')) ? $request->input('academicYear') : '';
        $status = is_string($request->input('status')) ? $request->input('status') : '';

        $items = $this->enrollments->listForAdmin($search, $program, $yearLevel, $semester, $academicYear, $status);

        Response::success(['enrollments' => $items]);
    }

    public function store(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $input = $request->all();

        $created = $this->enrollments->createByAdmin($input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'enrollment.create',
            'target_type' => 'enrollment',
            'target_id' => $created['id'],
        ]);

        Response::success(['enrollment' => $created], 'Enrollment record created.', 201);
    }

    public function show(Request $request): never
    {
        Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $record = $this->enrollments->findForAdmin($id);
        if ($record === null) {
            Response::error('NOT_FOUND', 'Enrollment record not found.', 404);
        }

        Response::success(['enrollment' => $record]);
    }

    public function update(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');
        $input = $request->all();

        $updated = $this->enrollments->updateByAdmin($id, $input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'enrollment.update',
            'target_type' => 'enrollment',
            'target_id' => (string) $id,
        ]);

        Response::success(['enrollment' => $updated], 'Enrollment updated.');
    }
}
