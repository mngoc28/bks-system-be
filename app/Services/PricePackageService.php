<?php

namespace App\Services;

use App\Repositories\PricePackageRepository\PricePackageRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class PricePackageService
{
    protected $pricePackageRepository;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependency (PricePackageRepositoryInterface)
     * using Dependency Injection.
     *
     * @param PricePackageRepositoryInterface $pricePackageRepository Handles data operations for price packages
     */
    public function __construct(PricePackageRepositoryInterface $pricePackageRepository)
    {
        $this->pricePackageRepository = $pricePackageRepository;
    }

    /**
     * Get all price packages
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllPricePackages(): array
    {
        try {
            $pricePackages = $this->pricePackageRepository->getAllPricePackages();
            return [
                'success' => true,
                'data' => $pricePackages,
                'message' => __('pricepackage.messages.fetch_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching price packages: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => __('pricepackage.messages.fetch_error')
            ];
        }
    }

    /**
     * Get price packages by room ID
     * @param int $roomId
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getPricePackagesByRoomId(int $roomId): array
    {
        try {
            $pricePackages = $this->pricePackageRepository->getPricePackagesByRoomId($roomId);
            return [
                'success' => true,
                'data' => $pricePackages,
                'message' => __('pricepackage.messages.fetch_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching price packages by room ID: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => __('pricepackage.messages.fetch_error')
            ];
        }
    }
}
