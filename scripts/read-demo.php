<?php
$demo = file_get_contents('backend/storage/data/demo.json');
if ($demo === false) {
    echo "No demo file" . PHP_EOL;
    exit;
}
echo $demo . PHP_EOL;
