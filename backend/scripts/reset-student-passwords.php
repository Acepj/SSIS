<?php

use SSIS\Repositories\UserRepository;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Must be run from CLI\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap/app.php';

$users = new UserRepository();
$known = [
    '2026-00123' => 'Maria Santos',
    '2026-00124' => 'Juan Dela Cruz',
    '2026-00125' => 'Ana Reyes',
];

foreach ($known as $id => $name) {
    $user = $users->findByUsername($id);
    if ($user === null) {
        fwrite(STDOUT, "Student $id ({$name}) not found.\n");
        continue;
    }
    $hash = password_hash('DemoPass123!', PASSWORD_DEFAULT);
    $users->update((string) $user['id'], ['password_hash' => $hash]);
    fwrite(STDOUT, "Reset password for $id ({$name}) to 'DemoPass123!'\n");
}
