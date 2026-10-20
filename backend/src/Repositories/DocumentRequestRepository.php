<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to document_requests.json. Request numbers are unique and
 * server-generated; ownership checks happen in the service layer.
 */
final class DocumentRequestRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.document_requests', 'document_requests.json'),
        );
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function all(): array
    {
        $records = array_values($this->store->read());
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $records;
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function findByStudentId(string $studentId): array
    {
        $records = array_values(array_filter(
            $this->store->read(),
            static fn (array $record): bool => ($record['student_id'] ?? null) === $studentId,
        ));
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $records;
    }

    public function findById(string $id): ?array
    {
        foreach ($this->store->read() as $record) {
            if (($record['id'] ?? null) === $id) {
                return $record;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data request fields (without id/timestamps/history)
     * @return array<string, mixed> the created record
     */
    public function create(array $data): array
    {
        $created = null;
        $this->store->transaction(function (array &$records) use ($data, &$created): void {
            $requestNumber = IdGenerator::requestCode();
            foreach ($records as $record) {
                if (($record['request_number'] ?? null) === $requestNumber) {
                    throw new \RuntimeException('Request number collision; retry.');
                }
            }

            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('req_'),
                'request_number' => $requestNumber,
                'student_id' => (string) $data['student_id'],
                'document_type' => (string) $data['document_type'],
                'purpose' => (string) $data['purpose'],
                'copies' => (int) $data['copies'],
                'delivery_method' => (string) ($data['delivery_method'] ?? 'Pickup'),
                'additional_instructions' => (string) ($data['additional_instructions'] ?? ''),
                'status' => 'Pending',
                'processing_notes' => '',
                'created_at' => $now,
                'updated_at' => $now,
                'status_history' => [[
                    'previous_status' => null,
                    'new_status' => 'Pending',
                    'changed_by' => (string) ($data['requested_by'] ?? ''),
                    'changed_by_name' => (string) ($data['requested_by_name'] ?? 'Student'),
                    'changed_at' => $now,
                    'note' => 'Request submitted.',
                ]],
            ];
            $records[] = $created;
        });

        return $created;
    }

    /** @param array<string, mixed> $changes */
    public function update(string $id, array $changes): array
    {
        $updated = null;
        $this->store->transaction(function (array &$records) use ($id, $changes, &$updated): void {
            foreach ($records as $index => $record) {
                if (($record['id'] ?? null) !== $id) {
                    continue;
                }

                $record = array_merge($record, $changes, ['updated_at' => gmdate('c')]);
                $records[$index] = $record;
                $updated = $record;

                return;
            }

            throw new NotFoundException('Document request not found.', 'NOT_FOUND');
        });

        return $updated;
    }
}
