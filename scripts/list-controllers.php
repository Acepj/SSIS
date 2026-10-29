<?php
$dir = 'backend/src/Controllers';
if (is_dir($dir)) {
    $files = scandir($dir);
    sort($files);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        echo $f . PHP_EOL;
    }
}
