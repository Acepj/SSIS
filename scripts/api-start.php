<?php
$f = file('backend/routes/api.php');
echo 'total lines: ' . count($f) . PHP_EOL;
$start = max(0, 0);
for ($i = $start; $i < min(30, count($f)); $i++) {
    echo ($i + 1) . ' | ' . $f[$i];
}
