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

    /** @return array<int, array<string, mixed>> admin list */
    public function listForAdmin(string $search = '', string $program = '', string $yearLevel = '', string $semester = '', string $active = ''): array
    {
        $all = $this->courses->all();
        $result = [];
        foreach ($all as $course) {
            if ($program !== '' && (string) ($course['program'] ?? '') !== $program) {
                continue;
            }
            if ($yearLevel !== '' && (string) ($course['year_level'] ?? '') !== $yearLevel) {
                continue;
            }
            if ($semester !== '' && (string) ($course['semester'] ?? '') !== $semester) {
                continue;
            }
            if ($active !== '' && (string) ($course['active'] ?? '') !== $active) {
                continue;
            }
            if ($search !== '' && !str_contains(strtolower((string) ($course['course_code'] ?? '')), strtolower($search))
                && !str_contains(strtolower((string) ($course['title'] ?? '')), strtolower($search))) {
                continue;
            }
            $result[] = $this->presentAdminCourse($course);
        }

        usort($result, static fn (array $a, array $b): int => strcmp($b['createdAt'], $a['createdAt']));

        return $result;
    }

    /** @param array<string, mixed> $input validated admin input */
    public function createByAdmin(array $input): array
    {
        $courseCode = strtoupper(trim((string) ($input['courseCode'] ?? '')));
        if ($courseCode === '') {
            throw new ValidationException('A course code is required.', ['courseCode' => 'Course code is required.']);
        }
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            throw new ValidationException('A title is required.', ['title' => 'Course title is required.']);
        }

        $created = $this->courses->create([
            'course_code' => $courseCode,
            'title' => $title,
            'description' => trim((string) ($input['description'] ?? '')),
            'program' => trim((string) ($input['program'] ?? '')),
            'year_level' => trim((string) ($input['yearLevel'] ?? '')),
            'semester' => trim((string) ($input['semester'] ?? '')),
            'active' => (bool) ($input['active'] ?? true),
        ]);

        return $this->presentAdminCourse($created);
    }

    /** @return array<string, mixed>|null */
    public function findForAdmin(string $id): ?array
    {
        $course = $this->courses->findById($id);
        if ($course === null) {
            return null;
        }

        return $this->presentAdminCourse($course);
    }

    /** @param array<string, mixed> $input */
    public function updateByAdmin(string $id, array $input): array
    {
        $course = $this->courses->findById($id);
        if ($course === null) {
            throw new NotFoundException('Course not found.', 'NOT_FOUND');
        }

        $changes = [];
        if (array_key_exists('courseCode', $input)) {
            $changes['course_code'] = strtoupper(trim((string) $input['courseCode']));
        }
        if (array_key_exists('title', $input)) {
            $changes['title'] = trim((string) $input['title']);
        }
        if (array_key_exists('description', $input)) {
            $changes['description'] = trim((string) $input['description']);
        }
        if (array_key_exists('program', $input)) {
            $changes['program'] = trim((string) $input['program']);
        }
        if (array_key_exists('yearLevel', $input)) {
            $changes['year_level'] = trim((string) $input['yearLevel']);
        }
        if (array_key_exists('semester', $input)) {
            $changes['semester'] = trim((string) $input['semester']);
        }
        if (array_key_exists('active', $input)) {
            $changes['active'] = (bool) $input['active'];
        }

        $updated = $this->courses->update($id, $changes);

        return $this->presentAdminCourse($updated);
    }

    /** Archive a course (soft delete). */
    public function archive(string $id): void
    {
        $course = $this->courses->findById($id);
        if ($course === null) {
            throw new NotFoundException('Course not found.', 'NOT_FOUND');
        }

        $this->courses->update($id, ['active' => false]);
    }

    /** @param array<string, mixed> $course */
    private function presentAdminCourse(array $course): array
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
            'createdAt' => (string) $course['created_at'],
            'updatedAt' => (string) $course['updated_at'],
        ];
    }
}
