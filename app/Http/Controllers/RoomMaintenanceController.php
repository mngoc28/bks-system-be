<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\RoomMaintenanceValidation;
use App\Services\RoomMaintenanceService;
use Illuminate\Http\Request;

class RoomMaintenanceController extends Controller
{
    protected RoomMaintenanceValidation $roomMaintenanceValidation;
    protected RoomMaintenanceService $roomMaintenanceService;

    public function __construct(
        RoomMaintenanceValidation $roomMaintenanceValidation,
        RoomMaintenanceService $roomMaintenanceService
    ) {
        $this->roomMaintenanceValidation = $roomMaintenanceValidation;
        $this->roomMaintenanceService = $roomMaintenanceService;
    }

    /**
     * List room maintenance records.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = $this->roomMaintenanceValidation->listValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $this->roomMaintenanceService->getList($validator->validated());

        return $this->successResponse(
            $data,
            __('room_maintenance.list_success'),
            HttpStatus::OK
        );
    }

    /**
     * Create room maintenance record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$request->filled('start_time') && $request->filled('start_date')) {
            $request->merge(['start_time' => $request->input('start_date')]);
        }

        if (!$request->filled('end_time') && $request->filled('end_date')) {
            $request->merge(['end_time' => $request->input('end_date')]);
        }

        $validator = $this->roomMaintenanceValidation->storeValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('room_maintenance.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $response = $this->roomMaintenanceService->create($validator->validated());

        if (! $response['success']) {
            return $this->errorResponse(
                $response['message'],
                $response['data'],
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $response['data'],
            $response['message'],
            HttpStatus::OK
        );
    }
}
