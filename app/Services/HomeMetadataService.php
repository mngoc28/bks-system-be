<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class HomeMetadataService
{
    public function __construct(
        private HomePageCacheService $homePageCacheService,
        private ProvincesService $provincesService,
        private PropertiesService $propertiesService,
        private TouristSpotService $touristSpotService,
    ) {
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, message: string}
     */
    public function getBootstrapMetadata(): array
    {
        try {
            $data = $this->homePageCacheService->rememberBootstrapMetadata(function (): array {
                $provincesResult = $this->provincesService->getAllProvinces();
                $propertyTypesResult = $this->propertiesService->getAllPropertyTypes();

                $touristSpotsRequest = Request::create('/', 'GET', [
                    'limit' => (int) config('homepage.bootstrap.tourist_spots_limit', 50),
                ]);
                $touristSpotsResult = $this->touristSpotService->listPublicSuggestions($touristSpotsRequest);

                return [
                    'provinces' => $provincesResult['data'] ?? [],
                    'property_types' => $propertyTypesResult['data'] ?? [],
                    'tourist_spots' => $touristSpotsResult['data'],
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'message' => __('province.messages.get_all_provinces_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error fetching homepage bootstrap metadata: ' . $throwable->getMessage(), [
                'trace' => $throwable->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('province.messages.get_all_provinces_failed'),
            ];
        }
    }
}
