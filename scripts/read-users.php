<?php
$u = file_get_contents('backend/storage/data/users.json');
if ($u === false) {
    echo "NO USERS FILE" . PHP_EOL;
    exit;
}
echo $u . PHP_EOL;
