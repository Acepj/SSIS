<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\NotFoundException;
use SSIS\Helpers\Config;
use SSIS\Repositories\DocumentRequestRepository;
use SSIS\Repositories\NotificationRepository;
use SSIS\Repositories\StudentRepository;

/**
 * Student-facing record operations: profile presentation, permitted profile
 * updates, and dashboard aggregation (counts derived from stored records).
 *
 * Only contact/profile fields are student-editable; student IDs, programs,
 * year levels and enrollment statuses are official records students can
 * never change.
 */
final class StudentService
{
    private readonly StudentRepository $students;
    private readonly DocumentRequestRepository $requests;
    private readonly NotificationRepository $notifications;

    public function __construct(
        ?StudentRepository $students = null,
        ?DocumentRequestRepository $requests = null,
        ?NotificationRepository $notifications = null,
    ) {
        $this->students = $students ?? new StudentRepository();
        $this->requests = $requests ?? new DocumentRequestRepository();
        $this->notifications = $notifications ?? new NotificationRepository();
    }

    /** @return array<string, mixed> */
    public function profileForUser(string $userId): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        return $this->presentStudent($student);
    }

    /** Updates only permitted contact fields. @param array<string, mixed> $input */
    public function updateProfile(string $userId, array $input): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $changes = [];
        if (array_key_exists('email', $input)) {
            $changes['email'] = trim((string) $input['email']);
        }
        if (array_key_exists('contactNumber', $input)) {
            $changes['contact_number'] = trim((string) $input['contactNumber']);
        }

        $updated = $this->students->update((string) $student['id'], $changes);

        return $this->presentStudent($updated);
    }

    /** Dashboard aggregation - every count comes from actual stored records. */
    public function dashboardForUser(string $userId): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $requests = $this->requests->findByStudentId((string) $student['student_id']);
        $notifications = $this->notifications->findByRecipient($userId);

        $pending = 0;
        $completed = 0;
        foreach ($requests as $request) {
            $status = (string) ($request['status'] ?? '');
            if ($status === 'Pending' || $status === 'Processing') {
                $pending++;
            } elseif ($status === 'Completed') {
                $completed++;
            }
        }

        $unread = count(array_filter($notifications, static fn (array $n): bool => ($n['read_at'] ?? null) === null));

        return [
            'academic' => [
                'year' => (string) Config::get('app.academic.year', ''),
                'semester' => (string) Config::get('app.academic.semester', ''),
            ],
            'enrollment' => $this->presentEnrollmentSummary($student),
            'counts' => [
                'pending' => $pending,
                'completed' => $completed,
                'unreadNotifications' => $unread,
            ],
            'recentRequests' => array_map(
                fn (array $request): array => $this->presentRequestSummary($request),
                array_slice($requests, 0, 5),
            ),
            'recentNotifications' => array_map(
                fn (array $notification): array => $this->presentNotification($notification),
                array_slice($notifications, 0, 5),
            ),
        ];
    }

    /** @param array<string, mixed> $student */
    private function presentStudent(array $student): array
    {
        return [
            'id' => (string) $student['id'],
            'studentId' => (string) $student['student_id'],
            'fullName' => (string) $student['full_name'],
            'email' => (string) $student['email'],
            'contactNumber' => (string) $student['contact_number'],
            'program' => (string) $student['program'],
            'yearLevel' => (string) $student['year_level'],
            'enrollmentStatus' => (string) $student['enrollment_status'],
        ];
    }

    /** @param array<string, mixed> $request */
    private function presentRequestSummary(array $request): array
    {
        return [
            'id' => (string) $request['id'],
            'requestNumber' => (string) $request['request_number'],
            'documentType' => (string) $request['document_type'],
            'status' => (string) $request['status'],
            'createdAt' => (string) $request['created_at'],
        ];
    }

    /** @param array<string, mixed> $notification */
    private function presentNotification(array $notification): array
    {
        return [
            'id' => (string) $notification['id'],
            'title' => (string) $notification['title'],
            'message' => (string) $notification['message'],
            'readAt' => $notification['read_at'] ?? null,
            'createdAt' => (string) $notification['created_at'],
        ];
    }

    /** @param array<string, mixed> $student */
    private function presentEnrollmentSummary(array $student): ?array
    {
        if (($student['enrollment_status'] ?? '') === 'Not Enrolled') {
            return null;
        }

        return [
            'status' => (string) $student['enrollment_status'],
            'term' => sprintf(
                '%s %s',
                (string) Config::get('app.academic.semester', ''),
                (string) Config::get('app.academic.year', ''),
            ),
        ];
    }
}
