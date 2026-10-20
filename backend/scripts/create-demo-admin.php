<?php

declare(strict_types=1);

/*
 * DEVELOPMENT-ONLY administrator provisioning.
 *
 * Creates (or resets) an administrator account in storage/data/users.json.
 * A random password is generated and printed ONCE when none is supplied, so
 * no publicly known default admin credentials ever ship with the project.
 *
 * Blocked when APP_ENV=production.
 *
 * Usage:
 *   php scripts/create-demo-admin.php --username=ssis.admin
 *   php scripts/create-demo-admin.php --username=ssis.admin --password=Str0ngPass! --name="Site Admin"
 */

use SSIS\Repositories\UserRepository;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap/app.php';

if (env('APP_ENV', 'production') === 'production') {
    fwrite(STDERR, "Refusing to run: APP_ENV is 'production'. Administrator provisioning is development-only.\n");
    exit(1);
}

$options = getopt('', ['username:', 'password::', 'name::', 'help']);
if (isset($options['help']) || !isset($options['username'])) {
    fwrite(STDOUT, "Usage: php scripts/create-demo-admin.php --username=<name> [--password=<pass>] [--name=\"Display Name\"]\n");
    exit(isset($options['help']) ? 0 : 1);
}

$username = trim((string) $options['username']);
if (preg_match('/^[A-Za-z0-9._@-]{3,64}$/', $username) !== 1) {
    fwrite(STDERR, "Invalid username: use 3-64 characters (letters, numbers, . _ - @).\n");
    exit(1);
}

$password = isset($options['password']) && $options['password'] !== false ? (string) $options['password'] : '';
$generated = false;
if ($password === '') {
    $password = bin2hex(random_bytes(12));
    $generated = true;
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters long.\n");
    exit(1);
}

$displayName = isset($options['name']) && $options['name'] !== false ? trim((string) $options['name']) : 'Administrator';

$users = new UserRepository();
$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = $users->findByUsername($username);

try {
    if ($existing !== null) {
        if (($existing['role'] ?? '') !== 'admin') {
            fwrite(STDERR, "Username '{$username}' already exists as a non-admin account. Choose another username.\n");
            exit(1);
        }
        $users->update((string) $existing['id'], [
            'password_hash' => $hash,
            'active' => true,
            'display_name' => $displayName,
        ]);
        $action = 'updated (password reset, reactivated)';
    } else {
        $users->create([
            'username' => $username,
            'password_hash' => $hash,
            'role' => 'admin',
            'active' => true,
            'display_name' => $displayName,
        ]);
        $action = 'created';
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Failed: ' . $exception->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "Administrator '{$username}' {$action}.\n");
if ($generated) {
    fwrite(STDOUT, "Generated password (shown once, store it safely): {$password}\n");
} else {
    fwrite(STDOUT, "Password was set from the provided --password value.\n");
}
fwrite(STDOUT, "Sign in at /login with identifier '{$username}'.\n");
