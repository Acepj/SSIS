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

    /** @return array<int, array<string, mixed>> admin list */
    public function listForAdmin(string $search = '', string $program = '', string $yearLevel = '', string $semester = '', string $academicYear = '', string $status = ''): array
    {
        $all = $this->enrollments->all();
        $result = [];
        foreach ($all as $enr) {
            if ($program !== '' && (string) ($enr['program'] ?? '') !== $program) {
                continue;
            }
            if ($yearLevel !== '' && (string) ($enr['year_level'] ?? '') !== $yearLevel) {
                continue;
            }
            if ($semester !== '' && (string) ($enr['semester'] ?? '') !== $semester) {
                continue;
            }
            if ($academicYear !== '' && (string) ($enr['academic_year'] ?? '') !== $academicYear) {
                continue;
            }
            if ($status !== '' && (string) ($enr['status'] ?? '') !== $status) {
                continue;
            }
            if ($search !== '' && !str_contains(strtolower((string) ($enr['student_id'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($enr['program'] ?? '')), strtolower($search))) {
                continue;
            }
            $result[] = $this->presentAdminEnrollment($enr);
        }

        usort($result, static fn (array $a, array $b): int => strcmp($b['createdAt'], $a['createdAt']));

        return $result;
    }

    /** @param array<string, mixed> $input validated admin input */
    public function createByAdmin(array $input): array
    {
        $student = $this->students->findByUserId((string) ($input['student_id'] ?? ''));
        if ($student === null) {
            throw new NotFoundException('Student not found.', 'NOT_FOUND');
        }

        $academicYear = trim((string) ($input['academicYear'] ?? ''));
        $semester = trim((string) ($input['semester'] ?? ''));
        if ($academicYear === '' || $semester === '') {
            throw new ValidationException('Academic year and semester are required.', ['academicYear' => 'Academic year required.', 'semester' => 'Semester required.']);
        }

        $created = $this->enrollments->create([
            'student_id' => (string) $student['student_id'],
            'academic_year' => $academicYear,
            'semester' => $semester,
            'program' => trim((string) ($input['program'] ?? $student['program'] ?? '')),
            'year_level' => trim((string) ($input['yearLevel'] ?? $student['year_level'] ?? '')),
            'status' => (string) ($input['status'] ?? 'Enrolled'),
        ]);

        return $this->presentAdminEnrollment($created);
    }

    /** @return array<string, mixed>|null */
    public function findForAdmin(string $id): ?array
    {
        $enr = $this->enrollments->findById($id);
        if ($enr === null) {
            return null;
        }

        return $this->presentAdminEnrollment($enr);
    }

    /** @param array<string, mixed> $input */
    public function updateByAdmin(string $id, array $input): array
    {
        $enr = $this->enrollments->findById($id);
        if ($enr === null) {
            throw new NotFoundException('Enrollment record not found.', 'NOT_FOUND');
        }

        $changes = [];
        if (array_key_exists('program', $input)) {
            $changes['program'] = trim((string) $input['program']);
        }
        if (array_key_exists('yearLevel', $input)) {
            $changes['year_level'] = trim((string) $input['yearLevel']);
        }
        if (array_key_exists('status', $input)) {
            $changes['status'] = trim((string) $input['status']);
        }

        $updated = $this->enrollments->update($id, $changes);

        return $this->presentAdminEnrollment($updated);
    }

    /** @param array<string, mixed> $enr */
    private function presentAdminEnrollment(array $enr): array
    {
        return [
            'id' => (string) $enr['id'],
            'studentId' => (string) $enr['student_id'],
            'academicYear' => (string) $enr['academic_year'],
            'semester' => (string) $enr['semester'],
            'program' => (string) $enr['program'],
            'yearLevel' => (string) $enr['year_level'],
            'status' => (string) $enr['status'],
            'createdAt' => (string) $enr['created_at'],
            'updatedAt' => (string) $enr['updated_at'],
        ];
    }
}
