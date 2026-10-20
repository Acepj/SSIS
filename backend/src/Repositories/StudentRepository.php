<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to students.json: student profiles linked to user accounts.
 * Official fields (student_id, program, year_level, enrollment_status) are
 * managed here as plain data; write rules live in the service layer (Phase 4+).
 */
final class StudentRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.students', 'students.json'),
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return array_values($this->store->read());
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

    public function findByUserId(string $userId): ?array
    {
        foreach ($this->store->read() as $record) {
            if (($record['user_id'] ?? null) === $userId) {
                return $record;
            }
        }

        return null;
    }

    /** Case-insensitive lookup by student number (e.g. "2026-00123"). */
    public function findByStudentId(string $studentId): ?array
    {
        $needle = strtoupper(trim($studentId));
        foreach ($this->store->read() as $record) {
            if (isset($record['student_id']) && strtoupper((string) $record['student_id']) === $needle) {
                return $record;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data student fields (without id/timestamps)
     * @return array<string, mixed> the created record
     */
    public function create(array $data): array
    {
        $studentId = strtoupper(trim((string) ($data['student_id'] ?? '')));

        $created = null;
        $this->store->transaction(function (array &$records) use ($data, $studentId, &$created): void {
            foreach ($records as $record) {
                if (strtoupper((string) ($record['student_id'] ?? '')) === $studentId) {
                    throw new ValidationException(
                        'A student with this student ID already exists.',
                        ['student_id' => 'This student ID is already in use.'],
                        'CONFLICT',
                        409,
                    );
                }
            }

            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('stu_'),
                'user_id' => (string) ($data['user_id'] ?? ''),
                'student_id' => $studentId,
                'full_name' => (string) ($data['full_name'] ?? ''),
                'email' => (string) ($data['email'] ?? ''),
                'contact_number' => (string) ($data['contact_number'] ?? ''),
                'program' => (string) ($data['program'] ?? ''),
                'year_level' => (string) ($data['year_level'] ?? ''),
                'enrollment_status' => (string) ($data['enrollment_status'] ?? 'Enrolled'),
                'created_at' => $now,
                'updated_at' => $now,
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

                if (isset($changes['student_id'])) {
                    $newStudentId = strtoupper(trim((string) $changes['student_id']));
                    foreach ($records as $other) {
                        if (($other['id'] ?? null) !== $id
                            && strtoupper((string) ($other['student_id'] ?? '')) === $newStudentId) {
                            throw new ValidationException(
                                'A student with this student ID already exists.',
                                ['student_id' => 'This student ID is already in use.'],
                                'CONFLICT',
                                409,
                            );
                        }
                    }
                    $changes['student_id'] = $newStudentId;
                }

                $record = array_merge($record, $changes, ['updated_at' => gmdate('c')]);
                $records[$index] = $record;
                $updated = $record;

                return;
            }

            throw new NotFoundException('Student record not found.', 'NOT_FOUND');
        });

        return $updated;
    }
}
