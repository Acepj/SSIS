<?php
echo 'php_version=' . PHP_VERSION . PHP_EOL;
echo 'doc_root=' . __DIR__ . PHP_EOL;
echo 'index_exists=' . (is_file(__DIR__ . '/public/index.php') ? 'yes' : 'no') . PHP_EOL;
