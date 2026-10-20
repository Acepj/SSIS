<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\EnrollmentService;

/** Read-only enrollment information for the signed-in student. */
final class EnrollmentController
{
    private readonly EnrollmentService $enrollments;

    public function __construct(?EnrollmentService $enrollments = null)
    {
        $this->enrollments = $enrollments ?? new EnrollmentService();
    }

    public function show(Request $request): never
    {
        $userId = Authorization::requireAuth();

        Response::success([
            'enrollment' => $this->enrollments->currentForUser($userId),
            'academic' => $this->enrollments->academicTerm(),
        ]);
    }
}
