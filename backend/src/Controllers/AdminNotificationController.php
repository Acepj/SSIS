<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\NotificationService;

/**
 * Administrator notifications management: create and send notifications to
 * students or groups.
 */
final class AdminNotificationController
{
    private readonly NotificationService $notifications;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?NotificationService $notifications = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->notifications = $notifications ?? new NotificationService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function index(Request $request): never
    {
        Authorization::requireAdmin();

        $search = is_string($request->input('search')) ? $request->input('search') : '';
        $recipient = is_string($request->input('recipient')) ? $request->input('recipient') : '';

        $items = $this->notifications->listForAdmin($search, $recipient);

        Response::success(['notifications' => $items]);
    }

    public function store(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $input = $request->all();

        $recipientUserId = (string) ($input['recipient_user_id'] ?? '');
        $title = trim((string) ($input['title'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));

        if ($recipientUserId === '') {
            throw new ValidationException('A recipient is required.', ['recipient_user_id' => 'Select a recipient.']);
        }
        if ($title === '') {
            throw new ValidationException('A title is required.', ['title' => 'Enter a notification title.']);
        }
        if ($message === '') {
            throw new ValidationException('A message is required.', ['message' => 'Enter a notification message.']);
        }

        $created = $this->notifications->createByAdmin($recipientUserId, $title, $message, $actorId);
        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'notification.create',
            'target_type' => 'notification',
            'target_id' => $created['id'],
        ]);

        Response::success(['notification' => $created], 'Notification sent.', 201);
    }
}
