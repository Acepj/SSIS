<?php
$files = [
    'EnrollmentService' => 'backend/src/Services/EnrollmentService.php',
    'CourseService'     => 'backend/src/Services/CourseService.php',
    'NotificationService' => 'backend/src/Services/NotificationService.php',
    'ReportService'     => 'backend/src/Services/ReportService.php',
];
foreach ($files as $name => $path) {
    $f = file($path);
    echo '=== ' . $name . ' ===' . PHP_EOL;
    echo 'total lines: ' . count($f) . PHP_EOL;
    $start = max(0, count($f) - 8);
    for ($i = $start; $i < count($f); $i++) {
        echo ($i + 1) . ' | ' . trim($f[$i]) . PHP_EOL;
    }
    echo PHP_EOL;
}
