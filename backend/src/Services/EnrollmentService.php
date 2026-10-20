<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Helpers\Config;
use SSIS\Repositories\EnrollmentRepository;
use SSIS\Repositories\StudentRepository;

/**
 * Enrollment information for students (read-only on the student side;
 * official enrollment management is an administrator function in Phase 5).
 */
final class EnrollmentService
{
    private readonly EnrollmentRepository $enrollments;
    private readonly StudentRepository $students;

    public function __construct(
        ?EnrollmentRepository $enrollments = null,
        ?StudentRepository $students = null,
    ) {
        $this->enrollments = $enrollments ?? new EnrollmentRepository();
        $this->students = $students ?? new StudentRepository();
    }

    /** Latest enrollment record for the signed-in student (or null). */
    public function currentForUser(string $userId): ?array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            return null;
        }

        $enrollment = $this->enrollments->findLatestForStudent((string) $student['student_id']);
        if ($enrollment === null) {
            return null;
        }

        return [
            'id' => (string) $enrollment['id'],
            'academicYear' => (string) $enrollment['academic_year'],
            'semester' => (string) $enrollment['semester'],
            'program' => (string) $enrollment['program'],
            'yearLevel' => (string) $enrollment['year_level'],
            'status' => (string) $enrollment['status'],
            'dateEnrolled' => (string) $enrollment['created_at'],
        ];
    }

    /** Academic term shown alongside enrollment info. */
    public function academicTerm(): array
    {
        return [
            'year' => (string) Config::get('app.academic.year', ''),
            'semester' => (string) Config::get('app.academic.semester', ''),
        ];
    }
}
