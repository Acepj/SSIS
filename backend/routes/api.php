<?php

declare(strict_types=1);

use SSIS\Controllers\AuthController;
use SSIS\Controllers\CourseController;
use SSIS\Controllers\DocumentRequestController;
use SSIS\Controllers\EnrollmentController;
use SSIS\Controllers\NotificationController;
use SSIS\Controllers\StudentController;
use SSIS\Controllers\AdminStudentController;
use SSIS\Controllers\AdminDocumentRequestController;
use SSIS\Controllers\AdminEnrollmentController;
use SSIS\Controllers\AdminCourseController;
use SSIS\Controllers\AdminNotificationController;
use SSIS\Controllers\AdminReportController;

/*
 * API route table. Each entry:
 *   [HTTP method, path pattern, [ControllerClass, action], [middleware...]]
 *
 * Middleware tokens:
 *   auth[:role]  - requires a valid active session (optionally a role,
 *                  e.g. auth:student)
 *   admin        - requires an active administrator (AdminMiddleware)
 *   csrf         - validates X-CSRF-Token on state-changing requests
 *   rate:<key>   - applies a configured rate limit (e.g. rate:login)
 *
 * Admin route group is appended in Phase 5.
 */

return [
    // --- Authentication -----------------------------------------------------
    ['POST', '/api/v1/auth/login', [AuthController::class, 'login'], ['csrf', 'rate:login']],
    ['POST', '/api/v1/auth/logout', [AuthController::class, 'logout'], ['csrf']],
    ['GET', '/api/v1/auth/me', [AuthController::class, 'me'], ['auth']],
    ['GET', '/api/v1/auth/csrf', [AuthController::class, 'csrf'], []],

    // --- Student portal -----------------------------------------------------
    ['GET', '/api/v1/student/dashboard', [StudentController::class, 'dashboard'], ['auth:student']],
    ['GET', '/api/v1/student/profile', [StudentController::class, 'profile'], ['auth:student']],
    ['PATCH', '/api/v1/student/profile', [StudentController::class, 'updateProfile'], ['auth:student', 'csrf']],
    ['GET', '/api/v1/student/enrollment', [EnrollmentController::class, 'show'], ['auth:student']],
    ['GET', '/api/v1/student/requests', [DocumentRequestController::class, 'index'], ['auth:student']],
    ['POST', '/api/v1/student/requests', [DocumentRequestController::class, 'store'], ['auth:student', 'csrf']],
    ['GET', '/api/v1/student/requests/{id}', [DocumentRequestController::class, 'show'], ['auth:student']],
    ['POST', '/api/v1/student/requests/{id}/cancel', [DocumentRequestController::class, 'cancel'], ['auth:student', 'csrf']],
    ['GET', '/api/v1/student/courses', [CourseController::class, 'index'], ['auth:student']],
    ['GET', '/api/v1/student/courses/{id}', [CourseController::class, 'show'], ['auth:student']],
    ['GET', '/api/v1/student/notifications', [NotificationController::class, 'index'], ['auth:student']],
    ['POST', '/api/v1/student/notifications/{id}/read', [NotificationController::class, 'markRead'], ['auth:student', 'csrf']],
    ['POST', '/api/v1/student/notifications/read-all', [NotificationController::class, 'markAllRead'], ['auth:student', 'csrf']],

    // --- Admin portal (Phase 5) ------------------------------------------------
    ['GET', '/api/v1/admin/requests', [AdminDocumentRequestController::class, 'index'], ['admin', 'auth']],
    ['GET', '/api/v1/admin/requests/{id}', [AdminDocumentRequestController::class, 'show'], ['admin', 'auth']],
    ['PATCH', '/api/v1/admin/requests/{id}/status', [AdminDocumentRequestController::class, 'updateStatus'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/students', [AdminStudentController::class, 'index'], ['admin', 'auth']],
    ['GET', '/api/v1/admin/students/{id}', [AdminStudentController::class, 'show'], ['admin', 'auth']],
    ['GET', '/api/v1/admin/students/{id}/enrollments', [AdminEnrollmentController::class, 'index'], ['admin', 'auth']],
    ['POST', '/api/v1/admin/enrollments', [AdminEnrollmentController::class, 'store'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/enrollments/{id}', [AdminEnrollmentController::class, 'show'], ['admin', 'auth']],
    ['PATCH', '/api/v1/admin/enrollments/{id}', [AdminEnrollmentController::class, 'update'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/courses', [AdminCourseController::class, 'index'], ['admin', 'auth']],
    ['POST', '/api/v1/admin/courses', [AdminCourseController::class, 'store'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/courses/{id}', [AdminCourseController::class, 'show'], ['admin', 'auth']],
    ['PUT', '/api/v1/admin/courses/{id}', [AdminCourseController::class, 'update'], ['admin', 'auth', 'csrf']],
    ['DELETE', '/api/v1/admin/courses/{id}', [AdminCourseController::class, 'archive'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/notifications', [AdminNotificationController::class, 'index'], ['admin', 'auth']],
    ['POST', '/api/v1/admin/notifications', [AdminNotificationController::class, 'store'], ['admin', 'auth', 'csrf']],
    ['GET', '/api/v1/admin/reports/summary', [AdminReportController::class, 'summary'], ['admin', 'auth']],
    ['GET', '/api/v1/admin/reports/requests.csv', [AdminReportController::class, 'exportCsv'], ['admin', 'auth']],
];
