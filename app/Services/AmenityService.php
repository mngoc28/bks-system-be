<?php

namespace App\Services;

use App\Repositories\AmenityRepository\AmenityRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Mpdf\Tag\A;

class AmenityService
{
    protected $amenityRepository;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependency (AmenityRepositoryInterface)
     * using Dependency Injection.
     *
     * @param AmenityRepositoryInterface $amenityRepository Handles data operations for amenities
     */
    public function __construct(AmenityRepositoryInterface $amenityRepository)
    {
        $this->amenityRepository = $amenityRepository;
    }

    /**
     * Get all amenities
     * @param Request $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllAmenities(Request $request): array
    {
        try {
            $amenities = $this->amenityRepository->getAllOrSearch($request);
            return [
                'success' => true,
                'data' => $amenities,
                'message' => __('amenity.messages.fetch_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching amenities: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => __('amenity.messages.fetch_error')
            ];
        }
    }

    /**
     * Get all amenities without pagination
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllAmenitiesWithoutPagination(): array
    {
        try {
            $amenities = $this->amenityRepository->getAllAmenities();
            return [
                'success' => true,
                'data' => $amenities,
                'message' => __('amenity.messages.fetch_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching all amenities: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => __('amenity.messages.fetch_error')
            ];
        }
    }

    /**
     * Get amenity by ID
     * @param int $id
     * @return object|null
     */
    public function getAmenityById(int $id): object|null
    {
        try {
            return $this->amenityRepository->find($id);
        } catch (Exception $e) {
            Log::error('Error fetching amenity by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new amenity
     * @param Request $request
     * @return object|null
     */
    public function createAmenity(Request $request): object|null
    {
        try {
            $data = $request->only(['name', 'created_by']);
            return $this->amenityRepository->create($data);
        } catch (Exception $e) {
            Log::error('Error creating amenity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing amenity
     * @param int $id
     * @param Request $request
     * @return object|null
     */
    public function updateAmenity(int $id, Request $request): object|null
    {
        try {
            $data = $request->only(['name', 'updated_by']);
            $this->amenityRepository->update($id, $data);
            return $this->amenityRepository->find($id);
        } catch (Exception $e) {
            Log::error('Error updating amenity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete amenity by ID
     * @param int $id
     * @return bool|null
     */
    public function deleteAmenity(int $id): bool|null
    {
        try {
            return $this->amenityRepository->delete($id);
        } catch (Exception $e) {
            Log::error('Error deleting amenity: ' . $e->getMessage());
            return null;
        }
    }
}
