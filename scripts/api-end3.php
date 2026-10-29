<?php
$f = file('backend/routes/api.php');
echo 'total lines: ' . count($f) . PHP_EOL;
$start = max(0, 50);
for ($i = $start; $i < count($f); $i++) {
    echo ($i + 1) . ' | ' . $f[$i];
}
