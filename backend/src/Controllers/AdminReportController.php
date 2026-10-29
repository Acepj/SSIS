<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\ActivityLogService;
use SSIS\Services\ReportService;

/**
 * Administrator reports: service statistics and CSV export.
 */
final class AdminReportController
{
    private readonly ReportService $reports;
    private readonly ActivityLogService $logs;

    public function __construct(
        ?ReportService $reports = null,
        ?ActivityLogService $logs = null,
    ) {
        $this->reports = $reports ?? new ReportService();
        $this->logs = $logs ?? new ActivityLogService();
    }

    public function summary(Request $request): never
    {
        Authorization::requireAdmin();

        $dateFrom = is_string($request->input('dateFrom')) ? $request->input('dateFrom') : '';
        $dateTo = is_string($request->input('dateTo')) ? $request->input('dateTo') : '';

        $summary = $this->reports->summary($dateFrom, $dateTo);

        Response::success(['summary' => $summary]);
    }

    public function exportCsv(Request $request): never
    {
        $actorId = Authorization::requireAdmin();

        $dateFrom = is_string($request->input('dateFrom')) ? $request->input('dateFrom') : '';
        $dateTo = is_string($request->input('dateTo')) ? $request->input('dateTo') : '';

        $csv = $this->reports->requestsCsv($dateFrom, $dateTo);

        $this->logs->log([
            'actor_user_id' => $actorId,
            'action' => 'report.export_csv',
            'target_type' => 'report',
            'target_id' => 'requests',
        ]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ssis_requests.csv"');
        header('Cache-Control: no-store');
        echo $csv;
        exit;
    }
}
