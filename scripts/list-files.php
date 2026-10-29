<?php
foreach (['frontend/src/components', 'frontend/src/features', 'frontend/src/pages/student', 'frontend/src/layouts'] as $dir) {
    echo '=== ' . $dir . ' ===' . PHP_EOL;
    $files = scandir($dir);
    sort($files);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        echo $f . PHP_EOL;
    }
    echo PHP_EOL;
}
