<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to activity_logs.json: administrative activity records (append-only audit log).
 */
final class ActivityLogRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.activity_logs', 'activity_logs.json'),
        );
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function all(): array
    {
        $records = array_values($this->store->read());
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? '')));

        return $records;
    }

    /** @return array<int, array<string, mixed>> filtered, newest first */
    public function findByActor(string $actorUserId): array
    {
        $records = array_values(array_filter(
            $this->store->read(),
            static fn (array $r): bool => ($r['actor_user_id'] ?? null) === $actorUserId,
        ));
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? '')));

        return $records;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $created = null;
        $this->store->transaction(function (array &$records) use ($data, &$created): void {
            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('act_'),
                'actor_user_id' => (string) ($data['actor_user_id'] ?? ''),
                'action' => (string) ($data['action'] ?? ''),
                'target_type' => (string) ($data['target_type'] ?? ''),
                'target_id' => (string) ($data['target_id'] ?? ''),
                'timestamp' => $now,
                'metadata' => (string) ($data['metadata'] ?? ''),
            ];
            $records[] = $created;
        });

        return $created;
    }
}
