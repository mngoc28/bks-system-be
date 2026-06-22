<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\RoomMaintenanceValidation;
use App\Services\RoomMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoomMaintenanceController extends Controller
{
    public function __construct(
        private readonly RoomMaintenanceValidation $roomMaintenanceValidation,
        private readonly RoomMaintenanceService $roomMaintenanceService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->roomMaintenanceValidation->listValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                null,
                HttpStatus::VALIDATION_ERROR,
                $validator->errors(),
            );
        }

        $data = $this->roomMaintenanceService->getList($validator->validated());

        return $this->successResponse(
            $data,
            __('room_maintenance.list_success'),
            HttpStatus::OK,
        );
    }

    public function conflictPreview(Request $request): JsonResponse
    {
        $validator = $this->roomMaintenanceValidation->conflictPreviewValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                null,
                HttpStatus::VALIDATION_ERROR,
                $validator->errors(),
            );
        }

        $validated = $validator->validated();
        $response = $this->roomMaintenanceService->previewCalendarConflicts(
            (int) $validated['room_id'],
            (string) $validated['start_date'],
            (string) $validated['end_date'],
        );

        return $this->mapServiceResponse($response, HttpStatus::OK);
    }

    public function show(int $id): JsonResponse
    {
        $response = $this->roomMaintenanceService->getById($id);

        return $this->mapServiceResponse($response, HttpStatus::OK);
    }

    public function store(Request $request): JsonResponse
    {
        $this->mergeLegacyDateFields($request);

        $validator = $this->roomMaintenanceValidation->storeValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                null,
                HttpStatus::VALIDATION_ERROR,
                $validator->errors(),
            );
        }

        $response = $this->roomMaintenanceService->create($validator->validated());

        return $this->mapServiceResponse($response, HttpStatus::OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->roomMaintenanceValidation->updateValidation($request, $id);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                null,
                HttpStatus::VALIDATION_ERROR,
                $validator->errors(),
            );
        }

        $response = $this->roomMaintenanceService->update($id, $validator->validated());

        return $this->mapServiceResponse($response, HttpStatus::OK);
    }

    private function mergeLegacyDateFields(Request $request): void
    {
        if (! $request->filled('start_time') && $request->filled('start_date')) {
            $request->merge(['start_time' => $request->input('start_date')]);
        }

        if (! $request->filled('end_time') && $request->filled('end_date')) {
            $request->merge(['end_time' => $request->input('end_date')]);
        }
    }

    /**
     * @param array{success: bool, data: mixed, message: string, code?: string} $response
     */
    private function mapServiceResponse(array $response, HttpStatus $successStatus): JsonResponse
    {
        if ($response['success']) {
            return $this->successResponse($response['data'], $response['message'], $successStatus);
        }

        $code = $response['code'] ?? null;
        $httpStatus = match ($code) {
            'MAINTENANCE_NOT_FOUND' => HttpStatus::NOT_FOUND,
            'MAINTENANCE_UNAUTHORIZED' => HttpStatus::FORBIDDEN,
            'MAINTENANCE_CALENDAR_CONFLICT' => HttpStatus::CONFLICT,
            'MAINTENANCE_INVALID_TRANSITION', 'MAINTENANCE_VALIDATION_ERROR' => HttpStatus::VALIDATION_ERROR,
            default => HttpStatus::BAD_REQUEST,
        };

        return $this->errorResponse(
            $response['message'],
            $code,
            $httpStatus,
            $response['data'] ?? null,
        );
    }
}
