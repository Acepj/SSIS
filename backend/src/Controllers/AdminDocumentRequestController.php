<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Exceptions\AuthorizationException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\DocumentRequestService;

/**
 * Administrator document request processing workspace: list, view details,
 * update status with transition validation, add processing notes.
 */
final class AdminDocumentRequestController
{
    private readonly DocumentRequestService $requests;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?DocumentRequestService $requests = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->requests = $requests ?? new DocumentRequestService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function index(Request $request): never
    {
        Authorization::requireAdmin();

        $search = is_string($request->input('search')) ? $request->input('search') : '';
        $status = is_string($request->input('status')) ? $request->input('status') : '';
        $documentType = is_string($request->input('documentType')) ? $request->input('documentType') : '';
        $dateFrom = is_string($request->input('dateFrom')) ? $request->input('dateFrom') : '';
        $dateTo = is_string($request->input('dateTo')) ? $request->input('dateTo') : '';

        $items = $this->requests->listForAdmin($search, $status, $documentType, $dateFrom, $dateTo);

        Response::success(['requests' => $items]);
    }

    public function show(Request $request): never
    {
        Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');

        $requestRecord = $this->requests->detailsForAdmin($id);
        if ($requestRecord === null) {
            Response::error('NOT_FOUND', 'Document request not found.', 404);
        }

        Response::success(['request' => $requestRecord]);
    }

    public function updateStatus(Request $request): never
    {
        $actorId = Authorization::requireAdmin();
        $id = (string) $request->routeParam('id');
        $input = $request->all();

        $newStatus = (string) ($input['status'] ?? '');
        $note = trim((string) ($input['note'] ?? ''));

        if ($newStatus === '') {
            throw new ValidationException('Status is required.', ['status' => 'Select a new status.']);
        }

        $updated = $this->requests->updateStatusForAdmin($id, $newStatus, $actorId, $note);

        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'request.status_change',
            'target_type' => 'document_request',
            'target_id' => $id,
            'metadata' => json_encode(['new_status' => $newStatus, 'note' => $note]),
        ]);

        Response::success(['request' => $updated], 'Request status updated.');
    }
}
