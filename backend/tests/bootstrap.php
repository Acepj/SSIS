<?php

declare(strict_types=1);

/*
 * PHPUnit bootstrap: loads the application bootstrap (autoloading,
 * configuration, error handling). Test artifacts never touch the real
 * development storage: SSIS_STORAGE_PATH is redirected to a temp folder
 * unless the caller already set it (integration tests set their own).
 */

if (getenv('SSIS_STORAGE_PATH') === false) {
    $testStorage = sys_get_temp_dir() . '/ssis-phpunit-storage';
    putenv('SSIS_STORAGE_PATH=' . $testStorage);
    $_ENV['SSIS_STORAGE_PATH'] = $testStorage;
}

require dirname(__DIR__) . '/bootstrap/app.php';
