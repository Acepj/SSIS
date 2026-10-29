<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\CourseService;

/**
 * Administrator course management: create, view, update, archive courses.
 */
final class AdminCourseController
{
    private readonly CourseService $courses;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?CourseService $courses = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->courses = $courses ?? new CourseService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function index(Request $request): never
    {
        Authorization::requireAdmin();

        $search = is_string($request->input('search')) ? $request->input('search') : '';
        $program = is_string($request->input('program')) ? $request->input('program') : '';
        $yearLevel = is_string($request->input('yearLevel')) ? $request->input('yearLevel') : '';
        $semester = is_string($request->input('semester')) ? $request->input('semester') : '';
        $active = is_string($request->input('active')) ? $request->input('active') : '';

        $items = $this->courses->listForAdmin($search, $program, $yearLevel, $semester, $active);

        Response::success(['courses' => $items]);
    }

    public function store(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $input = $request->all();

        $created = $this->courses->createByAdmin($input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'course.create',
            'target_type' => 'course',
            'target_id' => $created['id'],
        ]);

        Response::success(['course' => $created], 'Course created.', 201);
    }

    public function show(Request $request): never
    {
        Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $course = $this->courses->findForAdmin($id);
        if ($course === null) {
            Response::error('NOT_FOUND', 'Course not found.', 404);
        }

        Response::success(['course' => $course]);
    }

    public function update(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');
        $input = $request->all();

        $updated = $this->courses->updateByAdmin($id, $input);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'course.update',
            'target_type' => 'course',
            'target_id' => (string) $id,
        ]);

        Response::success(['course' => $updated], 'Course updated.');
    }

    public function archive(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $this->courses->archive($id);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'course.archive',
            'target_type' => 'course',
            'target_id' => (string) $id,
        ]);

        Response::success(null, 'Course archived.');
    }
}
