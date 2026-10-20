<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\StorageException;
use SSIS\Helpers\Logger;

/**
 * JSON file persistence with concurrency safeguards:
 *
 * - Sidecar .lock file with flock() serializes ALL readers and writers,
 *   preventing lost updates between concurrent PHP processes.
 * - Writes go to a temp file first and are atomically renamed into place,
 *   so a crash mid-write can never truncate the live file.
 * - The previous good state is kept as <file>.bak; corrupt files are
 *   automatically recovered from it on read.
 * - Files/directories are created with restricted permissions (0600/0700).
 *
 * This class is the single seam to replace with a real database later.
 */
final class JsonStore
{
    private readonly string $filePath;
    private readonly string $lockPath;
    private readonly string $backupPath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->lockPath = $filePath . '.lock';
        $this->backupPath = $filePath . '.bak';

        $dir = dirname($filePath);
        if (!is_dir($dir) && !@mkdir($dir, (int) (\SSIS\Helpers\Config::get('storage.dir_permissions', 0700)), true) && !is_dir($dir)) {
            throw new StorageException('Unable to create the storage directory.');
        }
    }

    /** Reads all records. Returns [] when the file does not exist yet. */
    public function read(): array
    {
        $handle = $this->acquireLock();
        try {
            return $this->readUnlocked();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Atomically reads-modifies-writes records under an exclusive lock.
     * The mutator receives the records by reference and must not return a value.
     *
     * @param callable(array<int|string, mixed>): void $mutator
     * @return array<int|string, mixed> the stored records after the mutation
     */
    public function transaction(callable $mutator): array
    {
        $handle = $this->acquireLock();
        try {
            $records = $this->readUnlocked();
            $mutator($records);
            $this->writeUnlocked($records);

            return $records;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** Replaces the whole file (used by seed/backup tooling). */
    public function write(array $records): void
    {
        $handle = $this->acquireLock();
        try {
            $this->writeUnlocked($records);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return resource */
    private function acquireLock()
    {
        $handle = @fopen($this->lockPath, 'c+');
        if ($handle === false) {
            throw new StorageException('Unable to open the data store lock.');
        }

        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new StorageException('Unable to acquire the data store lock.');
        }

        return $handle;
    }

    /** Caller must hold the lock. */
    private function readUnlocked(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $raw = @file_get_contents($this->filePath);
        if ($raw === false) {
            throw new StorageException('Unable to read the data store.');
        }
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $this->recoverUnlocked();
        }

        return $decoded;
    }

    /** Caller must hold the lock. Restores the last good backup. */
    private function recoverUnlocked(): array
    {
        Logger::warning('Corrupt JSON file detected; attempting recovery from backup.', [
            'file' => basename($this->filePath),
        ]);

        if (is_file($this->backupPath)) {
            $raw = @file_get_contents($this->backupPath);
            $decoded = $raw === false ? null : json_decode($raw, true);
            if (is_array($decoded)) {
                $this->writeUnlocked($decoded);
                Logger::info('Recovered data file from backup.', ['file' => basename($this->filePath)]);

                return $decoded;
            }
        }

        throw new StorageException('The stored data file is corrupted and no valid backup is available.');
    }

    /** Caller must hold the lock. Atomic: temp file + rename, backup kept. */
    private function writeUnlocked(array $records): void
    {
        $json = json_encode(
            $records,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR,
        );
        if ($json === false) {
            throw new StorageException('The data could not be encoded for storage.');
        }

        $dir = dirname($this->filePath);
        $tmpPath = $dir . DIRECTORY_SEPARATOR . '.' . basename($this->filePath) . '.' . bin2hex(random_bytes(6)) . '.tmp';

        if (@file_put_contents($tmpPath, $json, LOCK_EX) === false) {
            throw new StorageException('Unable to write the data store.');
        }
        @chmod($tmpPath, (int) (\SSIS\Helpers\Config::get('storage.file_permissions', 0600)));

        // Keep the previous state only when it is still valid JSON, so a
        // corrupt main file can never overwrite a good backup.
        if (is_file($this->filePath)) {
            $current = @file_get_contents($this->filePath);
            if ($current !== false && is_array(json_decode($current, true))) {
                @copy($this->filePath, $this->backupPath);
            }
        }

        if (!@rename($tmpPath, $this->filePath)) {
            @unlink($tmpPath);
            throw new StorageException('Unable to save the data store.');
        }
    }
}
