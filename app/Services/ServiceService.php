<?php

namespace App\Services;

use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class ServiceService
{
    /**
     * Repository layer that handles data operations for services.
     */
    protected $serviceRepository;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependency (ServiceRepositoryInterface)
     * using Dependency Injection.
     *
     * @param ServiceRepositoryInterface $serviceRepository Handles data operations for services
     */
    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Get all services or search services
     * @param mixed $request
     * @return object
     */
    public function getAllServices($request): ?object
    {
        try {
            return $this->serviceRepository->getAllOrSearch($request);
        } catch (Exception $e) {
            Log::error('Error fetching services: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get service by id
     * @param int $id
     * @return object
     */
    public function getServiceById($id): ?object
    {
        try {
            return $this->serviceRepository->find($id);
        } catch (Exception $e) {
            Log::error('Error fetching service by ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create service
     * @param array $data
     * @return object
     */
    public function createService($request): ?object
    {
        try {
            return $this->serviceRepository->create([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'created_by' => Auth::id(),
            ]);
        } catch (Exception $e) {
            Log::error('Error creating service: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update service
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateService($request, $id): bool
    {
        try {
            return $this->serviceRepository->update($id, [
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'updated_by' => Auth::id(),
            ]);
        } catch (Exception $e) {
            Log::error('Error updating service: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete service
     * @param int $id
     * @return bool
     */
    public function deleteService($id): bool
    {
        try {
            return $this->serviceRepository->delete($id);
        } catch (Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get all services without pagination
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllServicesWithoutPagination(): array
    {
        try {
            $services = $this->serviceRepository->getAllServices();

            return [
                'success' => true,
                'data' => $services,
                'message' => __('service.messages.fetch_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching all services: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => __('service.messages.fetch_error')
            ];
        }
    }
}
