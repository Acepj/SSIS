<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to enrollment.json: one active record per student per term.
 */
final class EnrollmentRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.enrollment', 'enrollment.json'),
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

    /** Latest enrollment record for a student (by created_at). */
    public function findLatestForStudent(string $studentId): ?array
    {
        $records = array_values(array_filter(
            $this->store->read(),
            static fn (array $record): bool => ($record['student_id'] ?? null) === $studentId,
        ));

        if ($records === []) {
            return null;
        }

        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $records[0];
    }

    /** @return array<int, array<string, mixed>> */
    public function findByStudentId(string $studentId): array
    {
        return array_values(array_filter(
            $this->store->read(),
            static fn (array $record): bool => ($record['student_id'] ?? null) === $studentId,
        ));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $created = null;
        $this->store->transaction(function (array &$records) use ($data, &$created): void {
            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('enr_'),
                'student_id' => (string) $data['student_id'],
                'academic_year' => (string) $data['academic_year'],
                'semester' => (string) $data['semester'],
                'program' => (string) ($data['program'] ?? ''),
                'year_level' => (string) ($data['year_level'] ?? ''),
                'status' => (string) ($data['status'] ?? 'Enrolled'),
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

                $record = array_merge($record, $changes, ['updated_at' => gmdate('c')]);
                $records[$index] = $record;
                $updated = $record;

                return;
            }

            throw new NotFoundException('Enrollment record not found.', 'NOT_FOUND');
        });

        return $updated;
    }
}
