<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Repositories\ActivityLogRepository;

/**
 * Administrative activity logging. Records who performed each administrative
 * action and when it occurred, for audit and dashboard display.
 */
final class ActivityLogService
{
    private readonly ActivityLogRepository $logs;

    public function __construct(?ActivityLogRepository $logs = null)
    {
        $this->logs = $logs ?? new ActivityLogRepository();
    }

    /** Log an administrative action. @param array<string, mixed> $data */
    public function log(array $data): array
    {
        return $this->logs->create($data);
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function all(): array
    {
        return $this->logs->all();
    }

    /** @return array<int, array<string, mixed>> filtered by actor, newest first */
    public function findByActor(string $actorUserId): array
    {
        return $this->logs->findByActor($actorUserId);
    }
}
