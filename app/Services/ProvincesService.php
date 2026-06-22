<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Repositories\ProvincesRepository\ProvincesRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProvincesService
{
    protected $provincesRepository;
    protected $helper;
    /**
     * Constructor method
     * @param ProvincesRepositoryInterface $provincesRepository
     */
    public function __construct(ProvincesRepositoryInterface $provincesRepository, Helper $helper)
    {
        $this->provincesRepository = $provincesRepository;
        $this->helper = $helper;
    }

    /**
     * Get a province by ID
     * @param int $id
     * @return object
     */
    public function getProvinceById(int $id): object
    {
        try {
            $province = $this->provincesRepository->detailProvince($id);
            return $province;
        } catch (\Exception $e) {
            Log::error(__('province.messages.show_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return (object)[];
        }
    }

    /**
     * Get all provinces
     * @param Request $request
     * @return array
     */
    public function listProvinces($request): array
    {
        try {
            $provinces = $this->provincesRepository->listProvinces($request);
            return [
                'success' => true,
                'data'    => $provinces,
                'message' => __('province.messages.search_success'),
            ];
        } catch (\Exception $e) {
            log::error(__('province.messages.search_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'data'    => [],
                'message' => __('province.messages.search_failed'),
            ];
        }
    }

    /**
     * Get all provinces types
     * @return object | null
     */
    public function getAllProvincesTypes(): object | null
    {
        try {
            return $this->provincesRepository->getAllProvincesTypes();
        } catch (\Exception $e) {
            Log::error(__('province.messages.get_all_provinces_types_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    // ====== The functions below are APIs for the end user ======
    /**
     * Get all provinces for homepage
     * @return array
     */
    public function getAllProvinces(): array
    {
        try {
            $provinces = $this->provincesRepository->getAllProvinces();
            return [
                'success' => true,
                'data'    => $provinces,
                'message' => __('province.messages.get_all_provinces_success'),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching provinces: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'data'    => [],
                'message' => __('province.messages.get_all_provinces_failed'),
            ];
        }
    }

    /**
     * Update a province's details
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateProvince(int $id, array $data): array
    {
        try {
            $province = $this->provincesRepository->find($id);
            if (!$province) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('province.messages.not_found'),
                ];
            }

            $this->provincesRepository->update($id, $data);
            \Illuminate\Support\Facades\Cache::forget('all_provinces');
            app(HomePageCacheService::class)->bumpMetadataCacheVersion();

            return [
                'success' => true,
                'data' => $this->provincesRepository->detailProvince($id),
                'message' => __('province.messages.update_success'),
            ];
        } catch (\Exception $e) {
            Log::error('Error updating province: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'data' => null,
                'message' => __('province.messages.update_error'),
            ];
        }
    }
}
