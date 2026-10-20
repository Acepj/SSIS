<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\NotificationService;

/**
 * Student notification endpoints. Every call is scoped to the
 * authenticated user - students can never read or mutate others' data.
 */
final class NotificationController
{
    private readonly NotificationService $notifications;

    public function __construct(?NotificationService $notifications = null)
    {
        $this->notifications = $notifications ?? new NotificationService();
    }

    public function index(Request $request): never
    {
        $userId = Authorization::requireAuth();

        Response::success([
            'notifications' => $this->notifications->listForUser($userId),
            'unreadCount' => $this->notifications->unreadCount($userId),
        ]);
    }

    public function markRead(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $id = (string) $request->routeParam('id');

        Response::success(['notification' => $this->notifications->markRead($userId, $id)]);
    }

    public function markAllRead(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $updated = $this->notifications->markAllRead($userId);

        Response::success(['updated' => $updated], 'All notifications marked as read.');
    }
}
