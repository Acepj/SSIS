<?php

declare(strict_types=1);

namespace SSIS\Repositories;

use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\IdGenerator;

/**
 * Access to users.json: login identifiers, password hashes, roles, status.
 * Password hashes never leave this layer through API responses (presenters
 * in AuthService build user payloads explicitly).
 */
final class UserRepository
{
    private readonly JsonStore $store;

    public function __construct(?JsonStore $store = null)
    {
        $this->store = $store ?? new JsonStore(
            (string) Config::get('storage.data_path') . '/' . (string) Config::get('storage.files.users', 'users.json'),
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return array_values($this->store->read());
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

    /** Case-insensitive login lookup. */
    public function findByUsername(string $username): ?array
    {
        $needle = strtolower(trim($username));
        foreach ($this->store->read() as $record) {
            if (isset($record['username']) && strtolower((string) $record['username']) === $needle) {
                return $record;
            }
        }

        return null;
    }

    /**
     * @param array{username: string, password_hash: string, role: string, active?: bool, display_name?: string} $data
     * @return array<string, mixed> the created record
     */
    public function create(array $data): array
    {
        $username = strtolower(trim($data['username']));
        $role = $data['role'];

        if (!in_array($role, ['student', 'admin'], true)) {
            throw new ValidationException('Invalid account role.', ['role' => 'Unsupported role.']);
        }

        $created = null;
        $this->store->transaction(function (array &$records) use ($data, $username, $role, &$created): void {
            foreach ($records as $record) {
                if (isset($record['username']) && strcasecmp((string) $record['username'], $username) === 0) {
                    throw new ValidationException(
                        'An account with this username already exists.',
                        ['username' => 'This username is already taken.'],
                        'CONFLICT',
                        409,
                    );
                }
            }

            $now = gmdate('c');
            $created = [
                'id' => IdGenerator::publicId('usr_'),
                'username' => $username,
                'password_hash' => $data['password_hash'],
                'role' => $role,
                'active' => (bool) ($data['active'] ?? true),
                'display_name' => (string) ($data['display_name'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $records[] = $created;
        });

        return $created;
    }

    /** @param array<string, mixed> $changes subset of stored fields */
    public function update(string $id, array $changes): array
    {
        $updated = null;
        $this->store->transaction(function (array &$records) use ($id, $changes, &$updated): void {
            foreach ($records as $index => $record) {
                if (($record['id'] ?? null) !== $id) {
                    continue;
                }

                if (isset($changes['role']) && !in_array($changes['role'], ['student', 'admin'], true)) {
                    throw new ValidationException('Invalid account role.', ['role' => 'Unsupported role.']);
                }
                if (isset($changes['username'])) {
                    $newUsername = strtolower(trim((string) $changes['username']));
                    foreach ($records as $other) {
                        if (($other['id'] ?? null) !== $id
                            && strcasecmp((string) ($other['username'] ?? ''), $newUsername) === 0) {
                            throw new ValidationException(
                                'An account with this username already exists.',
                                ['username' => 'This username is already taken.'],
                                'CONFLICT',
                                409,
                            );
                        }
                    }
                    $changes['username'] = $newUsername;
                }

                $record = array_merge($record, $changes, ['updated_at' => gmdate('c')]);
                $records[$index] = $record;
                $updated = $record;

                return;
            }

            throw new \SSIS\Exceptions\NotFoundException('Account not found.', 'NOT_FOUND');
        });

        return $updated;
    }
}
