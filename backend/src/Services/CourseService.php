<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\NotFoundException;
use SSIS\Repositories\CourseRepository;

/**
 * Course catalog rules. The student-facing catalog is read-only; write
 * operations are exercised by administrators (Phase 5).
 */
final class CourseService
{
    private readonly CourseRepository $courses;

    public function __construct(?CourseRepository $courses = null)
    {
        $this->courses = $courses ?? new CourseRepository();
    }

    /**
     * Active courses with optional search/filters.
     *
     * @return array<int, array<string, mixed>>
     */
    public function catalog(array $filters): array
    {
        $records = array_filter($this->courses->all(), static fn (array $c): bool => ($c['active'] ?? false) === true);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $needle = strtolower($search);
            $records = array_filter($records, static fn (array $c): bool =>
                str_contains(strtolower((string) ($c['course_code'] ?? '')), $needle)
                || str_contains(strtolower((string) ($c['title'] ?? '')), $needle));
        }

        foreach (['program' => 'program', 'yearLevel' => 'year_level', 'semester' => 'semester'] as $filterKey => $field) {
            $value = trim((string) ($filters[$filterKey] ?? ''));
            if ($value !== '') {
                $records = array_filter($records, static fn (array $c): bool => (string) ($c[$field] ?? '') === $value);
            }
        }

        $presented = array_map(fn (array $c): array => $this->present($c), array_values($records));
        usort($presented, static fn (array $a, array $b): int => strcmp($a['courseCode'], $b['courseCode']));

        return $presented;
    }

    /** @return array<string, mixed> */
    public function details(string $courseId): array
    {
        $course = $this->courses->findById($courseId);
        if ($course === null || ($course['active'] ?? false) !== true) {
            throw new NotFoundException('Course not found.', 'NOT_FOUND');
        }

        return $this->present($course);
    }

    /** Distinct filter values derived from stored active courses. */
    public function filterOptions(): array
    {
        $programs = [];
        $yearLevels = [];
        $semesters = [];
        foreach ($this->courses->all() as $course) {
            if (($course['active'] ?? false) !== true) {
                continue;
            }
            $programs[(string) ($course['program'] ?? '')] = true;
            $yearLevels[(string) ($course['year_level'] ?? '')] = true;
            $semesters[(string) ($course['semester'] ?? '')] = true;
        }

        unset($programs[''], $yearLevels[''], $semesters['']);

        return [
            'programs' => array_keys($programs),
            'yearLevels' => array_keys($yearLevels),
            'semesters' => array_keys($semesters),
        ];
    }

    /** @param array<string, mixed> $course */
    private function present(array $course): array
    {
        return [
            'id' => (string) $course['id'],
            'courseCode' => (string) $course['course_code'],
            'title' => (string) $course['title'],
            'description' => (string) $course['description'],
            'program' => (string) $course['program'],
            'yearLevel' => (string) $course['year_level'],
            'semester' => (string) $course['semester'],
            'active' => (bool) $course['active'],
        ];
    }
}
