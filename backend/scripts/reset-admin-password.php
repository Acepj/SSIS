<?php
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$data = json_decode(file_get_contents('backend/storage/data/users.json'), true);
foreach ($data as &$user) {
    if ($user['username'] === 'ssis.admin') {
        $user['password_hash'] = $hash;
        $user['updated_at'] = date('c');
    }
}
file_put_contents('backend/storage/data/users.json', json_encode($data, JSON_PRETTY_PRINT));
echo 'Admin password updated to: admin123' . PHP_EOL;
