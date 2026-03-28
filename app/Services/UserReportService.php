<?php

namespace App\Services;

use App\Enums\HttpStatus;
use App\Repositories\UserReportRepository\UserReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserReportService
{
    public function __construct(
        private readonly UserReportRepositoryInterface $userReportRepository
    ) {
    }

    /**
     * Create a new user report record.
     *
     * @param array $data
     * @return array{success: bool, message: string, data: mixed, code?: int}
     */
    public function createReport(array $data): array
    {
        DB::beginTransaction();

        try {
            $payload = [
                'reporter_id' => $data['reporter_id'],
                'reported_user_id' => $data['reported_user_id'],
                'booking_id' => $data['booking_id'] ?? null,
                'type' => $data['type'],
                'title' => $data['title'],
                'description' => $data['description'],
                'severity' => $data['severity'],
                'status' => $data['status'],
                'admin_note' => $data['admin_note'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $report = $this->userReportRepository->create($payload);

            DB::commit();

            return [
                'success' => true,
                'message' => __('user_report.create_success'),
                'data' => $report,
                'code' => HttpStatus::CREATED,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to create user report', [
                'error' => $exception->getMessage(),
                'payload' => $data,
            ]);

            return [
                'success' => false,
                'message' => __('user_report.create_failed'),
                'data' => null,
            ];
        }
    }
}
