<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\EnrollmentService;
use SSIS\Services\StudentService;
use SSIS\Validators\StudentValidator;

/**
 * Student self-service endpoints: dashboard, profile, enrollment summary.
 * Every value is derived from stored records for the authenticated user -
 * the browser never supplies identity or ownership.
 */
final class StudentController
{
    private readonly StudentService $students;
    private readonly EnrollmentService $enrollments;
    private readonly StudentValidator $validator;

    public function __construct(
        ?StudentService $students = null,
        ?EnrollmentService $enrollments = null,
        ?StudentValidator $validator = null,
    ) {
        $this->students = $students ?? new StudentService();
        $this->enrollments = $enrollments ?? new EnrollmentService();
        $this->validator = $validator ?? new StudentValidator();
    }

    public function dashboard(Request $request): never
    {
        $userId = Authorization::requireAuth();

        Response::success($this->students->dashboardForUser($userId));
    }

    public function profile(Request $request): never
    {
        $userId = Authorization::requireAuth();

        Response::success(['student' => $this->students->profileForUser($userId)]);
    }

    public function updateProfile(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $input = $this->validator->validateProfileUpdate($request->all());

        Response::success(
            ['student' => $this->students->updateProfile($userId, $input)],
            'Profile updated successfully.',
        );
    }
}
