<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Repositories\ActivityLogRepository;
use SSIS\Repositories\DocumentRequestRepository;
use SSIS\Repositories\StudentRepository;

/**
 * Administrator reporting: service statistics and CSV export.
 */
final class ReportService
{
    private readonly DocumentRequestRepository $requests;
    private readonly StudentRepository $students;
    private readonly ActivityLogRepository $logs;

    public function __construct(
        ?DocumentRequestRepository $requests = null,
        ?StudentRepository $students = null,
        ?ActivityLogRepository $logs = null,
    ) {
        $this->requests = $requests ?? new DocumentRequestRepository();
        $this->students = $students ?? new StudentRepository();
        $this->logs = $logs ?? new ActivityLogRepository();
    }

    /**
     * Aggregate statistics for the reporting period.
     * @param string $dateFrom ISO date filter lower bound
     * @param string $dateTo ISO date filter upper bound
     * @return array<string, int|array>
     */
    public function summary(string $dateFrom = '', string $dateTo = ''): array
    {
        $records = $this->requests->all();

        $activeRequests = 0;
        $pendingCount = 0;
        $processingCount = 0;
        $readyCount = 0;
        $completedCount = 0;
        $rejectedCount = 0;
        $cancelledCount = 0;

        foreach ($records as $record) {
            $status = (string) ($record['status'] ?? '');
            if ($status === 'Pending') {
                $pendingCount++;
            } elseif ($status === 'Processing') {
                $processingCount++;
            } elseif ($status === 'Ready for Pickup') {
                $readyCount++;
            } elseif ($status === 'Completed') {
                $completedCount++;
            } elseif ($status === 'Rejected') {
                $rejectedCount++;
            } elseif ($status === 'Cancelled') {
                $cancelledCount++;
            }
        }

        $totalStudents = count($this->students->all());

        return [
            'period' => [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ],
            'requests' => [
                'total' => count($records),
                'pending' => $pendingCount,
                'processing' => $processingCount,
                'readyForPickup' => $readyCount,
                'completed' => $completedCount,
                'rejected' => $rejectedCount,
                'cancelled' => $cancelledCount,
            ],
            'students' => [
                'total' => $totalStudents,
            ],
            'activity' => [
                'totalLogEntries' => count($this->logs->all()),
            ],
        ];
    }

    /**
     * Build a UTF-8 CSV of document requests.
     * @param string $dateFrom ISO date filter lower bound
     * @param string $dateTo ISO date filter upper bound
     * @return string
     */
    public function requestsCsv(string $dateFrom = '', string $dateTo = ''): string
    {
        $records = $this->requests->all();

        $filtered = [];
        foreach ($records as $record) {
            $createdAt = (string) ($record['created_at'] ?? '');
            if ($dateFrom !== '' && $createdAt < $dateFrom) {
                continue;
            }
            if ($dateTo !== '' && $createdAt > $dateTo . 'T23:59:59+00:00') {
                continue;
            }
            $filtered[] = $record;
        }

        $headers = ['Request Number', 'Student ID', 'Student Name', 'Document Type', 'Purpose', 'Copies', 'Delivery Method', 'Status', 'Created At', 'Updated At'];
        $rows = [];
        foreach ($filtered as $record) {
            $rows[] = [
                (string) ($record['request_number'] ?? ''),
                (string) ($record['student_id'] ?? ''),
                (string) ($record['student_name'] ?? ''),
                (string) ($record['document_type'] ?? ''),
                (string) ($record['purpose'] ?? ''),
                (string) ($record['copies'] ?? 0),
                (string) ($record['delivery_method'] ?? 'Pickup'),
                (string) ($record['status'] ?? ''),
                (string) ($record['created_at'] ?? ''),
                (string) ($record['updated_at'] ?? ''),
            ];
        }

        $handle = fopen('php://temp', 'w');
        fputcsv($handle, $headers, ',', '"');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"');
        }
        rewind($handle);

        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
