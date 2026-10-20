<?php

declare(strict_types=1);

/*
 * DEVELOPMENT-ONLY sample data seeding.
 *
 * Creates demo student accounts (users.json + students.json) with random
 * passwords that are printed once. Idempotent: existing records are skipped.
 * Blocked when APP_ENV=production.
 *
 * Usage:
 *   php scripts/seed-demo-data.php
 *
 * Creates demo student accounts plus enrollment records, courses, sample
 * document requests (varied statuses) and notifications for demonstration.
 */

use SSIS\Repositories\CourseRepository;
use SSIS\Repositories\DocumentRequestRepository;
use SSIS\Repositories\EnrollmentRepository;
use SSIS\Repositories\NotificationRepository;
use SSIS\Repositories\StudentRepository;
use SSIS\Repositories\UserRepository;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap/app.php';

if (env('APP_ENV', 'production') === 'production') {
    fwrite(STDERR, "Refusing to run: APP_ENV is 'production'. Seeding is development-only.\n");
    exit(1);
}

$demoStudents = [
    [
        'student_id' => '2026-00123',
        'full_name' => 'Maria Santos',
        'email' => 'maria.santos@student.demo.edu',
        'contact_number' => '+63 917 555 0123',
        'program' => 'BS Information Technology',
        'year_level' => '3rd Year',
    ],
    [
        'student_id' => '2026-00124',
        'full_name' => 'Juan Dela Cruz',
        'email' => 'juan.delacruz@student.demo.edu',
        'contact_number' => '+63 917 555 0124',
        'program' => 'BS Computer Science',
        'year_level' => '2nd Year',
    ],
    [
        'student_id' => '2026-00125',
        'full_name' => 'Ana Reyes',
        'email' => 'ana.reyes@student.demo.edu',
        'contact_number' => '+63 917 555 0125',
        'program' => 'BS Business Administration',
        'year_level' => '4th Year',
    ],
];

$users = new UserRepository();
$students = new StudentRepository();
$credentials = [];
$skipped = 0;

foreach ($demoStudents as $demo) {
    if ($users->findByUsername($demo['student_id']) !== null
        || $students->findByStudentId($demo['student_id']) !== null) {
        $skipped++;
        continue;
    }

    $password = bin2hex(random_bytes(9));
    $user = $users->create([
        'username' => $demo['student_id'],
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'student',
        'active' => true,
        'display_name' => $demo['full_name'],
    ]);

    $students->create([
        'user_id' => $user['id'],
        'student_id' => $demo['student_id'],
        'full_name' => $demo['full_name'],
        'email' => $demo['email'],
        'contact_number' => $demo['contact_number'],
        'program' => $demo['program'],
        'year_level' => $demo['year_level'],
        'enrollment_status' => 'Enrolled',
    ]);

    $credentials[] = [$demo['student_id'], $demo['full_name'], $password];
}

fwrite(STDOUT, sprintf("Seeded %d demo student account(s); %d already existed.\n", count($credentials), $skipped));

if ($credentials !== []) {
    fwrite(STDOUT, "\nStudent sign-in credentials (shown once - store them safely):\n");
    fwrite(STDOUT, str_pad('Student ID', 14) . str_pad('Name', 20) . "Password\n");
    fwrite(STDOUT, str_repeat('-', 50) . "\n");
    foreach ($credentials as [$studentId, $name, $password]) {
        fwrite(STDOUT, str_pad($studentId, 14) . str_pad($name, 20) . $password . "\n");
    }
    fwrite(STDOUT, "\nSign in at /login using the Student ID as identifier.\n");
}

$academicYear = (string) env('ACADEMIC_YEAR', '2026-2027');
$semester = (string) env('ACADEMIC_SEMESTER', 'First Semester');

// --- Enrollment records ----------------------------------------------------
$enrollments = new EnrollmentRepository();
$enrolled = 0;
foreach ($demoStudents as $demo) {
    if ($enrollments->findLatestForStudent($demo['student_id']) !== null) {
        continue;
    }
    $enrollments->create([
        'student_id' => $demo['student_id'],
        'academic_year' => $academicYear,
        'semester' => $semester,
        'program' => $demo['program'],
        'year_level' => $demo['year_level'],
        'status' => 'Enrolled',
    ]);
    $enrolled++;
}
fwrite(STDOUT, "Seeded {$enrolled} enrollment record(s).\n");

// --- Course catalog --------------------------------------------------------
$courses = new CourseRepository();
$demoCourses = [
    ['IT 101', 'Introduction to Information Technology', 'Fundamentals of computing systems, software, and networks.', 'BS Information Technology', '1st Year', 'First Semester'],
    ['IT 210', 'Data Structures and Algorithms', 'Core problem-solving techniques and algorithm analysis.', 'BS Information Technology', '2nd Year', 'First Semester'],
    ['IT 315', 'Web Systems Development', 'Server-side and client-side web application development.', 'BS Information Technology', '3rd Year', 'First Semester'],
    ['CS 120', 'Object-Oriented Programming', 'Design and implementation using OOP principles.', 'BS Computer Science', '2nd Year', 'First Semester'],
    ['CS 230', 'Discrete Mathematics', 'Logic, sets, combinatorics, and graph theory for computing.', 'BS Computer Science', '2nd Year', 'First Semester'],
    ['BA 110', 'Principles of Management', 'Foundations of planning, organizing, and leading organizations.', 'BS Business Administration', '1st Year', 'First Semester'],
    ['BA 250', 'Business Finance', 'Financial decision-making and capital management.', 'BS Business Administration', '3rd Year', 'First Semester'],
    ['GE 150', 'Ethics', 'Moral reasoning applied to professional and civic life.', 'All Programs', '1st Year', 'First Semester'],
    ['GE 160', 'Communication Skills', 'Written and oral communication for academic and workplace settings.', 'All Programs', '1st Year', 'First Semester'],
    ['IT 320', 'Systems Integration and Architecture', 'Designing integrated enterprise systems.', 'BS Information Technology', '3rd Year', 'Second Semester'],
];
$courseCount = 0;
foreach ($demoCourses as [$code, $title, $description, $program, $yearLevel, $courseSemester]) {
    if ($courses->findByCode($code) !== null) {
        continue;
    }
    $courses->create([
        'course_code' => $code,
        'title' => $title,
        'description' => $description,
        'program' => $program,
        'year_level' => $yearLevel,
        'semester' => $courseSemester,
        'active' => true,
    ]);
    $courseCount++;
}
fwrite(STDOUT, "Seeded {$courseCount} course(s).\n");

// --- Sample document requests (varied statuses) ----------------------------
$users = new UserRepository();
$studentsRepo = new StudentRepository();
$requests = new DocumentRequestRepository();
$requestCount = 0;

$sampleRequests = [
    // [student number, document type, purpose, full status chain from Pending]
    ['2026-00123', 'Certificate of Enrollment', 'Scholarship application requirement.', ['Processing', 'Ready for Pickup', 'Completed']],
    ['2026-00123', 'Transcript of Records', 'On-the-job training application.', []],
    ['2026-00124', 'Certificate of Grades', 'Parental record copy.', ['Processing']],
    ['2026-00125', 'Certificate of Good Moral Character', 'Employment requirement.', ['Processing', 'Completed']],
];

foreach ($sampleRequests as [$studentNumber, $docType, $purpose, $chain]) {
    $existing = $requests->findByStudentId($studentNumber);
    $alreadySeeded = false;
    foreach ($existing as $existingRequest) {
        if (($existingRequest['document_type'] ?? '') === $docType) {
            $alreadySeeded = true;
            break;
        }
    }
    if ($alreadySeeded) {
        continue;
    }

    $student = $studentsRepo->findByStudentId($studentNumber);
    if ($student === null) {
        continue;
    }

    $created = $requests->create([
        'student_id' => $studentNumber,
        'document_type' => $docType,
        'purpose' => $purpose,
        'copies' => 1,
        'delivery_method' => 'Pickup',
        'additional_instructions' => '',
        'requested_by' => (string) $student['user_id'],
        'requested_by_name' => (string) $student['full_name'],
    ]);

    $current = $created;
    $history = (array) $created['status_history'];
    foreach ($chain as $status) {
        $history[] = [
            'previous_status' => (string) $current['status'],
            'new_status' => $status,
            'changed_by' => 'seed-script',
            'changed_by_name' => 'Student Services',
            'changed_at' => gmdate('c'),
            'note' => 'Sample status for demonstration.',
        ];
        $current = $requests->update((string) $created['id'], ['status' => $status, 'status_history' => $history]);
    }
    $requestCount++;
}
fwrite(STDOUT, "Seeded {$requestCount} sample document request(s).\n");

// --- Notifications ---------------------------------------------------------
$notifications = new NotificationRepository();
$notificationCount = 0;
foreach ($demoStudents as $demo) {
    $user = $users->findByUsername($demo['student_id']);
    if ($user === null || $notifications->findByRecipient((string) $user['id']) !== []) {
        continue;
    }

    $notifications->create([
        'recipient_user_id' => (string) $user['id'],
        'title' => 'Welcome to SSIS',
        'message' => 'Your student services account is ready. Submit and track document requests here.',
        'created_by' => 'system',
    ]);
    $notifications->create([
        'recipient_user_id' => (string) $user['id'],
        'title' => 'Enrollment confirmed',
        'message' => "Your enrollment for {$semester} {$academicYear} is recorded as Enrolled.",
        'created_by' => 'system',
    ]);
    $notificationCount += 2;
}
fwrite(STDOUT, "Seeded {$notificationCount} notification(s).\n");
