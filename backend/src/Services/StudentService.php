<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\NotFoundException;
use SSIS\Helpers\Config;
use SSIS\Repositories\DocumentRequestRepository;
use SSIS\Repositories\NotificationRepository;
use SSIS\Repositories\StudentRepository;
use SSIS\Repositories\UserRepository;

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
    private readonly UserRepository $users;

    public function __construct(
        ?StudentRepository $students = null,
        ?DocumentRequestRepository $requests = null,
        ?NotificationRepository $notifications = null,
        ?UserRepository $users = null,
    ) {
        $this->students = $students ?? new StudentRepository();
        $this->requests = $requests ?? new DocumentRequestRepository();
        $this->notifications = $notifications ?? new NotificationRepository();
        $this->users = $users ?? new UserRepository();
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

    /** Directory listing with optional search/program/status filters. */
    public function directory(string $search = '', string $program = '', string $status = ''): array
    {
        $students = $this->students->all();
        $usersById = [];
        foreach ($this->users->all() as $u) {
            $usersById[(string) $u['id']] = $u;
        }

        $result = [];
        foreach ($students as $student) {
            $user = $usersById[(string) ($student['user_id'] ?? '')] ?? [];
            if ($search !== '' && !str_contains(strtolower((string) ($student['full_name'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($student['student_id'] ?? '')), strtolower($search))) {
                continue;
            }
            if ($program !== '' && (string) ($student['program'] ?? '') !== $program) {
                continue;
            }
            if ($status !== '' && ($user['active'] ?? false) !== ($status === 'Active')) {
                continue;
            }

            $result[] = array_merge($this->presentStudent($student), [
                'accountStatus' => ($user['active'] ?? false) ? 'Active' : 'Inactive',
                'accountId' => (string) ($user['id'] ?? ''),
            ]);
        }

        usort($result, static fn (array $a, array $b): int => strcmp($a['studentId'], $b['studentId']));

        return $result;
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

    /** Create a student account + profile. @param array<string, mixed> $input */
    public function createByAdmin(array $input): array
    {
        $errors = [];
        $studentId = trim((string) ($input['student_id'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $program = trim((string) ($input['program'] ?? ''));
        $yearLevel = trim((string) ($input['year_level'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $passwordConfirm = (string) ($input['password_confirm'] ?? '');

        if ($studentId === '') {
            $errors['student_id'] = 'Student ID is required.';
        } elseif (preg_match('/^[A-Za-z0-9._-]{3,32}$/', $studentId) !== 1) {
            $errors['student_id'] = 'Invalid student ID format.';
        }
        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Password confirmation does not match.';
        }

        if ($errors !== []) {
            throw new ValidationException('Please review the submitted information.', $errors);
        }

        if ($this->students->findByStudentId($studentId) !== null) {
            throw new ValidationException('A student with this ID already exists.', ['student_id' => 'This student ID is already in use.'], 'CONFLICT', 409);
        }
        $existingUser = $this->users->findByUsername($studentId);
        if ($existingUser !== null) {
            throw new ValidationException('A student with this ID already exists.', ['student_id' => 'This student ID is already in use.'], 'CONFLICT', 409);
        }

        $user = $this->users->create([
            'username' => $studentId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'student',
            'active' => true,
            'display_name' => $fullName,
        ]);

        $student = $this->students->create([
            'user_id' => (string) $user['id'],
            'student_id' => $studentId,
            'full_name' => $fullName,
            'email' => $email,
            'contact_number' => (string) ($input['contact_number'] ?? ''),
            'program' => $program,
            'year_level' => $yearLevel,
            'enrollment_status' => 'Enrolled',
        ]);

        return array_merge($this->presentStudent($student), [
            'accountStatus' => 'Active',
            'accountId' => (string) $user['id'],
        ]);
    }

    /** Read student + account details for admin. @param string $id students.json id */
    public function findForAdmin(string $id): ?array
    {
        $student = $this->students->findById($id);
        if ($student === null) {
            return null;
        }

        $user = $this->users->findByUserId((string) $student['user_id']);

        return array_merge($this->presentStudent($student), [
            'accountStatus' => $user !== null ? (($user['active'] ?? false) ? 'Active' : 'Inactive') : 'Inactive',
            'accountId' => $user !== null ? (string) $user['id'] : '',
        ]);
    }

    /** Update student profile + permitted account fields. @param array<string, mixed> $input */
    public function updateByAdmin(string $id, array $input): array
    {
        $student = $this->students->findById($id);
        if ($student === null) {
            throw new NotFoundException('Student not found.', 'NOT_FOUND');
        }

        $changes = [];
        if (array_key_exists('email', $input)) {
            $email = trim((string) $input['email']);
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new ValidationException('A valid email address is required.', ['email' => 'Enter a valid email address.']);
            }
            $changes['email'] = $email;
        }
        if (array_key_exists('contactNumber', $input)) {
            $changes['contact_number'] = trim((string) $input['contactNumber']);
        }
        if (array_key_exists('program', $input)) {
            $changes['program'] = trim((string) $input['program']);
        }
        if (array_key_exists('yearLevel', $input)) {
            $changes['year_level'] = trim((string) $input['yearLevel']);
        }

        $updated = $this->students->update((string) $student['id'], $changes);

        return array_merge($this->presentStudent($updated), [
            'accountStatus' => 'Active',
            'accountId' => (string) ($this->users->findByUserId((string) $updated['user_id'])['id'] ?? ''),
        ]);
    }

    /** Activate or deactivate the linked user account. */
    public function setActive(string $id, bool $active): void
    {
        $student = $this->students->findById($id);
        if ($student === null) {
            throw new NotFoundException('Student not found.', 'NOT_FOUND');
        }

        $user = $this->users->findByUserId((string) $student['user_id']);
        if ($user === null) {
            throw new NotFoundException('Linked account not found.', 'NOT_FOUND');
        }

        $this->users->update((string) $user['id'], ['active' => $active]);
    }

    /** @param array<string, mixed> $student */
    private function presentAdminStudent(array $student): array
    {
        return $this->presentStudent($student);
    }
}
