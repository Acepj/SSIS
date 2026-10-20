<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\NotFoundException;
use SSIS\Repositories\NotificationRepository;

/**
 * Notification delivery and read state. Students can only ever see and
 * mark read their OWN notifications - ownership is verified on every call.
 */
final class NotificationService
{
    private readonly NotificationRepository $notifications;

    public function __construct(?NotificationRepository $notifications = null)
    {
        $this->notifications = $notifications ?? new NotificationRepository();
    }

    /** @return array<int, array<string, mixed>> */
    public function listForUser(string $userId): array
    {
        return array_map(
            fn (array $notification): array => $this->present($notification),
            $this->notifications->findByRecipient($userId),
        );
    }

    public function markRead(string $userId, string $notificationId): array
    {
        $notification = $this->notifications->findById($notificationId);
        if ($notification === null || ($notification['recipient_user_id'] ?? null) !== $userId) {
            throw new NotFoundException('Notification not found.', 'NOT_FOUND');
        }

        return $this->present($this->notifications->markRead($notificationId));
    }

    public function markAllRead(string $userId): int
    {
        return $this->notifications->markAllReadForRecipient($userId);
    }

    public function unreadCount(string $userId): int
    {
        return count(array_filter(
            $this->notifications->findByRecipient($userId),
            static fn (array $notification): bool => ($notification['read_at'] ?? null) === null,
        ));
    }

    /** @param array<string, mixed> $notification */
    private function present(array $notification): array
    {
        return [
            'id' => (string) $notification['id'],
            'title' => (string) $notification['title'],
            'message' => (string) $notification['message'],
            'readAt' => $notification['read_at'] ?? null,
            'createdAt' => (string) $notification['created_at'],
        ];
    }
}
