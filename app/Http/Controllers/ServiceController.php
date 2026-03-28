<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ServiceValidation;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Service layer that handles business logic for services.
     * Validation layer that handles request data validation for services.
     */
    protected ServiceValidation $serviceValidation;
    protected ServiceService $serviceService;
    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependencies (RoomsService and RoomsValidation)
     * using Dependency Injection.
     *
     * @param ServiceService $serviceService       Handles business logic for services
     * @param ServiceValidation $serviceValidation Validates input data for services
     */
    public function __construct(ServiceValidation $serviceValidation, ServiceService $serviceService)
    {
        $this->serviceValidation = $serviceValidation;
        $this->serviceService = $serviceService;
    }

    /**
     * get all services
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->serviceValidation->searchServiceValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->serviceService->getAllServices($request);

        if ($result === null) {
            return $this->errorResponse(
                __('service.messages.fetch_failed'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result,
            __('service.messages.fetch_success')
        );
    }

    /**
     * Get all services without pagination
     *
     * @return JsonResponse
     */
    public function getAllServices(): JsonResponse
    {
        $result = $this->serviceService->getAllServicesWithoutPagination();

        if (!$result['success']) {
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

    /**
     * get service details
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $validatorId = $this->serviceValidation->show($id);
        if ($validatorId->fails()) {
            return $this->validateError(
                $validatorId->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->serviceService->getServiceById($id);
        if ($result === null) {
            $statusCode = HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                __('service.messages.fetch_failed'),
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result,
            __('service.messages.fetch_success')
        );
    }

    /**
     * Create a new service.
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {

        $validator = $this->serviceValidation->store($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->serviceService->createService($request);

        if ($result === null) {
            return $this->errorResponse(
                __('service.messages.create_failed'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result,
            __('service.messages.create_success')
        );
    }

    /**
     * update service
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validatorId = $this->serviceValidation->update($request, $id);
        if ($validatorId->fails()) {
            return $this->validateError(
                $validatorId->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->serviceService->updateService($request, $id);
        if ($result === null) {
            $statusCode = HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                __('service.messages.update_failed'),
                null,
                $statusCode
            );
        }
        return $this->successResponse(
            $result,
            __('service.messages.update_success')
        );
    }

    /**
     * delete service
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $validatorId = $this->serviceValidation->show($id);
        if ($validatorId->fails()) {
            return $this->validateError(
                $validatorId->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->serviceService->deleteService($id);

        if ($result === false) {
            $statusCode = HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                __('service.messages.delete_failed'),
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            null,
            __('service.messages.delete_success')
        );
    }
}
