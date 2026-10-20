<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\CourseService;

/**
 * Course catalog (read-only for students; administration arrives in Phase 5).
 */
final class CourseController
{
    private readonly CourseService $courses;

    public function __construct(?CourseService $courses = null)
    {
        $this->courses = $courses ?? new CourseService();
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => is_string($request->input('search')) ? $request->input('search') : '',
            'program' => is_string($request->input('program')) ? $request->input('program') : '',
            'yearLevel' => is_string($request->input('yearLevel')) ? $request->input('yearLevel') : '',
            'semester' => is_string($request->input('semester')) ? $request->input('semester') : '',
        ];

        Response::success([
            'courses' => $this->courses->catalog($filters),
            'filters' => $this->courses->filterOptions(),
        ]);
    }

    public function show(Request $request): never
    {
        $id = (string) $request->routeParam('id');

        Response::success(['course' => $this->courses->details($id)]);
    }
}
