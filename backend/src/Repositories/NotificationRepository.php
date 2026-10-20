<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to notifications.json: per-recipient messages with read state.
 */
final class NotificationRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.notifications', 'notifications.json'),
        );
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function all(): array
    {
        $records = array_values($this->store->read());
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $records;
    }

    /** @return array<int, array<string, mixed>> newest first */
    public function findByRecipient(string $userId): array
    {
        $records = array_values(array_filter(
            $this->store->read(),
            static fn (array $record): bool => ($record['recipient_user_id'] ?? null) === $userId,
        ));
        usort($records, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $records;
    }

    public function findById(string $id): ?array
    {
        foreach ($this->store->read() as $record) {
            if (($record['id'] ?? null) === $id) {
                return $record;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $created = null;
        $this->store->transaction(function (array &$records) use ($data, &$created): void {
            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('ntf_'),
                'recipient_user_id' => (string) $data['recipient_user_id'],
                'title' => (string) ($data['title'] ?? ''),
                'message' => (string) ($data['message'] ?? ''),
                'read_at' => null,
                'created_at' => $now,
                'created_by' => (string) ($data['created_by'] ?? 'system'),
            ];
            $records[] = $created;
        });

        return $created;
    }

    /** Marks a notification read (idempotent). Owner is verified by the caller. */
    public function markRead(string $id): array
    {
        $updated = null;
        $this->store->transaction(function (array &$records) use ($id, &$updated): void {
            foreach ($records as $index => $record) {
                if (($record['id'] ?? null) !== $id) {
                    continue;
                }

                if (($record['read_at'] ?? null) === null) {
                    $record['read_at'] = gmdate('c');
                    $records[$index] = $record;
                }
                $updated = $record;

                return;
            }

            throw new NotFoundException('Notification not found.', 'NOT_FOUND');
        });

        return $updated;
    }

    /** Marks all of a recipient's unread notifications read (idempotent). */
    public function markAllReadForRecipient(string $userId): int
    {
        $count = 0;
        $this->store->transaction(function (array &$records) use ($userId, &$count): void {
            $now = gmdate('c');
            foreach ($records as $index => $record) {
                if (($record['recipient_user_id'] ?? null) === $userId && ($record['read_at'] ?? null) === null) {
                    $record['read_at'] = $now;
                    $records[$index] = $record;
                    $count++;
                }
            }
        });

        return $count;
    }
}
