<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\DocumentRequestService;
use SSIS\Validators\DocumentRequestValidator;

/**
 * Student document request endpoints (submit, track, cancel).
 * The authenticated student is resolved from the session on every call;
 * ownership of each request is verified in the service layer.
 */
final class DocumentRequestController
{
    private readonly DocumentRequestService $requests;
    private readonly DocumentRequestValidator $validator;

    public function __construct(
        ?DocumentRequestService $requests = null,
        ?DocumentRequestValidator $validator = null,
    ) {
        $this->requests = $requests ?? new DocumentRequestService();
        $this->validator = $validator ?? new DocumentRequestValidator();
    }

    public function index(Request $request): never
    {
        $userId = Authorization::requireAuth();

        $items = $this->requests->listForStudent(
            $userId,
            is_string($request->input('status')) ? $request->input('status') : null,
            is_string($request->input('search')) ? $request->input('search') : null,
        );

        Response::success(['requests' => $items]);
    }

    public function store(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $input = $this->validator->validate($request->all());

        Response::success(
            ['request' => $this->requests->createForStudent($userId, $input)],
            'Your document request has been submitted.',
            201,
        );
    }

    public function show(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $id = (string) $request->routeParam('id');

        Response::success(['request' => $this->requests->detailsForStudent($userId, $id)]);
    }

    public function cancel(Request $request): never
    {
        $userId = Authorization::requireAuth();
        $id = (string) $request->routeParam('id');

        Response::success(
            ['request' => $this->requests->cancelForStudent($userId, $id)],
            'Your request has been cancelled.',
        );
    }
}
