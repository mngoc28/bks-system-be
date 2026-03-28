<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\UserReportValidation;
use App\Services\UserReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function __construct(
        private readonly UserReportService $userReportService,
        private readonly UserReportValidation $userReportValidation
    ) {
    }

    /**
     * Submit a user violation report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->userReportValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('user_report.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->userReportService->createReport($validator->validated());

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }
}
