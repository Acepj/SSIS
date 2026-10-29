<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\AuthorizationException;
use SSIS\Exceptions\NotFoundException;
use SSIS\Repositories\DocumentRequestRepository;
use SSIS\Repositories\StudentRepository;

/**
 * Document request workflow and status rules.
 *
 * Status model: Pending -> Processing -> Ready for Pickup -> Completed,
 * with Rejected possible from Pending/Processing and student cancellation
 * allowed only while a request is still Pending. Only administrators change
 * processing statuses; students may only submit and cancel eligible requests.
 */
final class DocumentRequestService
{
    /** Statuses a request may transition FROM => allowed next statuses. */
    public const ALLOWED_TRANSITIONS = [
        'Pending' => ['Processing', 'Rejected', 'Cancelled'],
        'Processing' => ['Ready for Pickup', 'Completed', 'Rejected'],
        'Ready for Pickup' => ['Completed'],
        'Completed' => [],
        'Rejected' => [],
        'Cancelled' => [],
    ];

    public const STUDENT_CANCELLABLE_STATUSES = ['Pending'];

    private readonly DocumentRequestRepository $requests;
    private readonly StudentRepository $students;

    public function __construct(
        ?DocumentRequestRepository $requests = null,
        ?StudentRepository $students = null,
    ) {
        $this->requests = $requests ?? new DocumentRequestRepository();
        $this->students = $students ?? new StudentRepository();
    }

    /** @param array<string, mixed> $input validated request data */
    public function createForStudent(string $userId, array $input): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $created = $this->requests->create([
            'student_id' => (string) $student['student_id'],
            'document_type' => (string) $input['documentType'],
            'purpose' => trim((string) $input['purpose']),
            'copies' => (int) $input['copies'],
            'delivery_method' => (string) ($input['deliveryMethod'] ?? 'Pickup'),
            'additional_instructions' => trim((string) ($input['additionalInstructions'] ?? '')),
            'requested_by' => $userId,
            'requested_by_name' => (string) $student['full_name'],
        ]);

        return $this->presentDetails($created);
    }

    /** @return array<int, array<string, mixed>> presented summaries */
    public function listForStudent(string $userId, ?string $statusFilter = null, ?string $search = null): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $records = $this->requests->findByStudentId((string) $student['student_id']);

        if ($statusFilter !== null && $statusFilter !== '' && $statusFilter !== 'All') {
            $records = array_filter($records, static fn (array $r): bool => ($r['status'] ?? '') === $statusFilter);
        }

        if ($search !== null && trim($search) !== '') {
            $needle = strtolower(trim($search));
            $records = array_filter($records, static fn (array $r): bool =>
                str_contains(strtolower((string) ($r['request_number'] ?? '')), $needle)
                || str_contains(strtolower((string) ($r['document_type'] ?? '')), $needle));
        }

        return array_map(fn (array $r): array => $this->presentSummary($r), array_values($records));
    }

    /** Ownership-checked details. */
    public function detailsForStudent(string $userId, string $requestId): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $request = $this->requests->findById($requestId);
        if ($request === null || ($request['student_id'] ?? null) !== $student['student_id']) {
            throw new NotFoundException('The requested record was not found.', 'NOT_FOUND');
        }

        return $this->presentDetails($request);
    }

    /** Student cancellation: only while Pending; recorded, never deleted. */
    public function cancelForStudent(string $userId, string $requestId): array
    {
        $student = $this->students->findByUserId($userId);
        if ($student === null) {
            throw new NotFoundException('No student profile is linked to this account.', 'STUDENT_PROFILE_NOT_FOUND');
        }

        $request = $this->requests->findById($requestId);
        if ($request === null || ($request['student_id'] ?? null) !== $student['student_id']) {
            throw new NotFoundException('The requested record was not found.', 'NOT_FOUND');
        }

        $status = (string) $request['status'];
        if (!in_array($status, self::STUDENT_CANCELLABLE_STATUSES, true)) {
            throw new AuthorizationException(
                'Only pending requests can be cancelled. Please contact Student Services for assistance.',
                'CANCELLATION_NOT_ALLOWED',
            );
        }

        $history = (array) ($request['status_history'] ?? []);
        $history[] = [
            'previous_status' => $status,
            'new_status' => 'Cancelled',
            'changed_by' => $userId,
            'changed_by_name' => (string) $student['full_name'],
            'changed_at' => gmdate('c'),
            'note' => 'Cancelled by student.',
        ];

        $updated = $this->requests->update($requestId, [
            'status' => 'Cancelled',
            'status_history' => $history,
        ]);

        return $this->presentDetails($updated);
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    /** @param array<string, mixed> $request */
    public function presentSummary(array $request): array
    {
        return [
            'id' => (string) $request['id'],
            'requestNumber' => (string) $request['request_number'],
            'documentType' => (string) $request['document_type'],
            'status' => (string) $request['status'],
            'createdAt' => (string) $request['created_at'],
            'updatedAt' => (string) $request['updated_at'],
        ];
    }

    /** @param array<string, mixed> $request */
    public function presentDetails(array $request): array
    {
        $history = array_map(static fn (array $entry): array => [
            'previousStatus' => $entry['previous_status'] ?? null,
            'newStatus' => (string) ($entry['new_status'] ?? ''),
            'changedByName' => (string) ($entry['changed_by_name'] ?? ''),
            'changedAt' => (string) ($entry['changed_at'] ?? ''),
            'note' => (string) ($entry['note'] ?? ''),
        ], (array) ($request['status_history'] ?? []));

        return [
            'id' => (string) $request['id'],
            'requestNumber' => (string) $request['request_number'],
            'documentType' => (string) $request['document_type'],
            'purpose' => (string) $request['purpose'],
            'copies' => (int) $request['copies'],
            'deliveryMethod' => (string) ($request['delivery_method'] ?? 'Pickup'),
            'additionalInstructions' => (string) ($request['additional_instructions'] ?? ''),
            'status' => (string) $request['status'],
            'processingNotes' => (string) ($request['processing_notes'] ?? ''),
            'createdAt' => (string) $request['created_at'],
            'updatedAt' => (string) $request['updated_at'],
            'statusHistory' => $history,
            'canCancel' => in_array((string) $request['status'], self::STUDENT_CANCELLABLE_STATUSES, true),
        ];
    }

    /** @return array<int, array<string, mixed>> admin list with student enrichment */
    public function listForAdmin(string $search = '', string $status = '', string $documentType = '', string $dateFrom = '', string $dateTo = ''): array
    {
        $records = $this->requests->all();
        $students = [];
        foreach ($this->students->all() as $s) {
            $students[(string) $s['student_id']] = $s;
        }

        $result = [];
        foreach ($records as $record) {
            if ($status !== '' && ($record['status'] ?? '') !== $status) {
                continue;
            }
            if ($documentType !== '' && ($record['document_type'] ?? '') !== $documentType) {
                continue;
            }
            $createdAt = (string) ($record['created_at'] ?? '');
            if ($dateFrom !== '' && $createdAt < $dateFrom) {
                continue;
            }
            if ($dateTo !== '' && $createdAt > $dateTo . 'T23:59:59+00:00') {
                continue;
            }

            $student = $students[(string) ($record['student_id'] ?? '')] ?? [];
            if ($search !== '' && !str_contains(strtolower((string) ($record['request_number'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($record['document_type'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($student['full_name'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($student['student_id'] ?? '')), strtolower($search))) {
                continue;
            }

            $result[] = [
                'id' => (string) $record['id'],
                'requestNumber' => (string) ($record['request_number'] ?? ''),
                'studentId' => (string) ($record['student_id'] ?? ''),
                'studentName' => (string) ($student['full_name'] ?? ''),
                'documentType' => (string) ($record['document_type'] ?? ''),
                'status' => (string) ($record['status'] ?? ''),
                'createdAt' => $createdAt,
                'updatedAt' => (string) ($record['updated_at'] ?? ''),
            ];
        }

        usort($result, static fn (array $a, array $b): int => strcmp($b['createdAt'], $a['createdAt']));

        return $result;
    }

    /** @return array<int, array<string, mixed>> admin list with student enrichment */
    public function detailsForAdmin(string $id): ?array
    {
        $record = $this->requests->findById($id);
        if ($record === null) {
            return null;
        }

        $student = null;
        foreach ($this->students->all() as $s) {
            if ((string) $s['student_id'] === (string) ($record['student_id'] ?? '')) {
                $student = $s;
                break;
            }
        }

        $history = array_map(static fn (array $entry): array => [
            'previousStatus' => $entry['previous_status'] ?? null,
            'newStatus' => (string) ($entry['new_status'] ?? ''),
            'changedByName' => (string) ($entry['changed_by_name'] ?? ''),
            'changedAt' => (string) ($entry['changed_at'] ?? ''),
            'note' => (string) ($entry['note'] ?? ''),
        ], (array) ($record['status_history'] ?? []));

        return [
            'id' => (string) $record['id'],
            'requestNumber' => (string) ($record['request_number'] ?? ''),
            'studentId' => (string) ($record['student_id'] ?? ''),
            'studentName' => $student !== null ? (string) $student['full_name'] : '',
            'documentType' => (string) ($record['document_type'] ?? ''),
            'purpose' => (string) ($record['purpose'] ?? ''),
            'copies' => (int) ($record['copies'] ?? 0),
            'deliveryMethod' => (string) ($record['delivery_method'] ?? 'Pickup'),
            'additionalInstructions' => (string) ($record['additional_instructions'] ?? ''),
            'status' => (string) ($record['status'] ?? ''),
            'processingNotes' => (string) ($record['processing_notes'] ?? ''),
            'createdAt' => (string) ($record['created_at'] ?? ''),
            'updatedAt' => (string) ($record['updated_at'] ?? ''),
            'statusHistory' => $history,
        ];
    }

    /**
     * Admin status transition with audit trail. Enforces permitted transitions.
     * @param string $actorId user id of the administrator performing the change
     * @param string $note processing note
     * @return array<string, mixed> updated request details
     */
    public function updateStatusForAdmin(string $id, string $newStatus, string $actorId, string $note = ''): array
    {
        $record = $this->requests->findById($id);
        if ($record === null) {
            throw new NotFoundException('Document request not found.', 'NOT_FOUND');
        }

        $currentStatus = (string) ($record['status'] ?? '');
        if (!self::canTransition($currentStatus, $newStatus)) {
            throw new AuthorizationException(
                'Invalid status transition from ' . $currentStatus . ' to ' . $newStatus . '.',
                'INVALID_TRANSITION',
            );
        }

        if ($currentStatus === $newStatus) {
            throw new ValidationException('Request is already in status ' . $newStatus . '.', ['status' => 'No change.']);
        }

        $history = (array) ($record['status_history'] ?? []);
        $history[] = [
            'previous_status' => $currentStatus,
            'new_status' => $newStatus,
            'changed_by' => $actorId,
            'changed_by_name' => 'Administrator',
            'changed_at' => gmdate('c'),
            'note' => $note,
        ];

        $updated = $this->requests->update($id, [
            'status' => $newStatus,
            'processing_notes' => $note !== '' ? $note : (string) ($record['processing_notes'] ?? ''),
            'status_history' => $history,
        ]);

        return $this->detailsForAdmin((string) $updated['id']);
    }
}
