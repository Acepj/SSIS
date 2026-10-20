<?php

declare(strict_types=1);

/*
 * JSON file storage locations and safeguards. The storage directory lives
 * OUTSIDE public/ so raw JSON files can never be served over HTTP.
 * SSIS_STORAGE_PATH exists for tests that need an isolated storage root.
 */

$root = rtrim((string) env('SSIS_STORAGE_PATH', dirname(__DIR__) . '/storage'), '/\\');

return [
    'path' => $root,
    'data_path' => $root . '/data',
    'logs_path' => $root . '/logs',
    'backups_path' => $root . '/backups',

    'files' => [
        'users' => 'users.json',
        'students' => 'students.json',
        'document_requests' => 'document_requests.json',
        'enrollment' => 'enrollment.json',
        'courses' => 'courses.json',
        'notifications' => 'notifications.json',
        'activity_logs' => 'activity_logs.json',
    ],

    // Restricted permissions applied where the platform supports them.
    'dir_permissions' => 0700,
    'file_permissions' => 0600,

    // How many timestamped backups backup-data.php keeps.
    'backup_keep' => 10,
];
