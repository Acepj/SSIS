<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to courses.json: the course catalog (active and archived).
 */
final class CourseRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.courses', 'courses.json'),
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

    /** Case-insensitive lookup by course code (e.g. "IT 101"). */
    public function findByCode(string $code): ?array
    {
        $needle = strtoupper(trim($code));
        foreach ($this->store->read() as $record) {
            if (strtoupper((string) ($record['course_code'] ?? '')) === $needle) {
                return $record;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $courseCode = strtoupper(trim((string) ($data['course_code'] ?? '')));

        $created = null;
        $this->store->transaction(function (array &$records) use ($data, $courseCode, &$created): void {
            foreach ($records as $record) {
                if (strtoupper((string) ($record['course_code'] ?? '')) === $courseCode) {
                    throw new ValidationException(
                        'A course with this code already exists.',
                        ['course_code' => 'This course code is already in use.'],
                        'CONFLICT',
                        409,
                    );
                }
            }

            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('crs_'),
                'course_code' => $courseCode,
                'title' => (string) ($data['title'] ?? ''),
                'description' => (string) ($data['description'] ?? ''),
                'program' => (string) ($data['program'] ?? ''),
                'year_level' => (string) ($data['year_level'] ?? ''),
                'semester' => (string) ($data['semester'] ?? ''),
                'active' => (bool) ($data['active'] ?? true),
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

                if (isset($changes['course_code'])) {
                    $newCode = strtoupper(trim((string) $changes['course_code']));
                    foreach ($records as $other) {
                        if (($other['id'] ?? null) !== $id
                            && strtoupper((string) ($other['course_code'] ?? '')) === $newCode) {
                            throw new ValidationException(
                                'A course with this code already exists.',
                                ['course_code' => 'This course code is already in use.'],
                                'CONFLICT',
                                409,
                            );
                        }
                    }
                    $changes['course_code'] = $newCode;
                }

                $record = array_merge($record, $changes, ['updated_at' => gmdate('c')]);
                $records[$index] = $record;
                $updated = $record;

                return;
            }

            throw new NotFoundException('Course not found.', 'NOT_FOUND');
        });

        return $updated;
    }
}
