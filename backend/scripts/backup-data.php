<?php

declare(strict_types=1);

/*
 * Creates a timestamped backup of all JSON data files and prunes old backups.
 *
 * Usage:
 *   php scripts/backup-data.php
 *
 * Recovery procedure:
 *   1. Stop the application.
 *   2. Copy the newest backup's *.json files back into storage/data/.
 *   3. Start the application again.
 * JsonStore additionally keeps a <file>.bak of the previous good state for
 * automatic recovery from corruption.
 */

use SSIS\Helpers\Config;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap/app.php';

/**
 * Recursively removes a directory (backup pruning).
 */
function ssis_remove_dir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            ssis_remove_dir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

$dataPath = (string) Config::get('storage.data_path');
$backupsPath = (string) Config::get('storage.backups_path');
$keep = (int) Config::get('storage.backup_keep', 10);

if (!is_dir($dataPath)) {
    fwrite(STDERR, "No data directory found at {$dataPath}.\n");
    exit(1);
}

if (!is_dir($backupsPath) && !@mkdir($backupsPath, (int) Config::get('storage.dir_permissions', 0700), true)) {
    fwrite(STDERR, "Unable to create backup directory at {$backupsPath}.\n");
    exit(1);
}

$target = $backupsPath . DIRECTORY_SEPARATOR . gmdate('Ymd-His');
if (!@mkdir($target, (int) Config::get('storage.dir_permissions', 0700), true)) {
    fwrite(STDERR, "Unable to create backup folder {$target}.\n");
    exit(1);
}

$copied = 0;
$files = glob($dataPath . DIRECTORY_SEPARATOR . '*.json') ?: [];
foreach ($files as $file) {
    $name = basename($file);
    if (@copy($file, $target . DIRECTORY_SEPARATOR . $name)) {
        $copied++;
    } else {
        fwrite(STDERR, "Warning: failed to copy {$name}\n");
    }
}

fwrite(STDOUT, "Backed up {$copied} data file(s) to storage/backups/" . basename($target) . "\n");

// Prune oldest backups beyond the configured retention.
$backups = [];
foreach (glob($backupsPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
    $backups[basename($dir)] = $dir;
}
ksort($backups);

$excess = count($backups) - $keep;
if ($excess > 0) {
    foreach (array_slice($backups, 0, $excess) as $dir) {
        ssis_remove_dir($dir);
        fwrite(STDOUT, 'Pruned old backup: ' . basename($dir) . "\n");
    }
}

exit($copied > 0 || $files === [] ? 0 : 1);
