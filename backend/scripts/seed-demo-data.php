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
 * Course/enrollment/request seeds are added in later phases alongside their
 * repositories and validators.
 */

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
