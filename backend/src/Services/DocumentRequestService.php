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
}
