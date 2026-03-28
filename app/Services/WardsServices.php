<?php

namespace App\Services;

use App\Repositories\WardsRepository\WardsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class WardsServices
{
    /**
     * Wards repository instance
     */
    protected WardsRepositoryInterface $wardsRepository;

    /**
     * Constructor
     * @param WardsRepositoryInterface $wardsRepository
     */
    public function __construct(WardsRepositoryInterface $wardsRepository)
    {
        $this->wardsRepository = $wardsRepository;
    }

    /**
     * get wards by province id
     * @param int $provinceId
     * @return object | null
     */
    public function getWardsByProvinceId(int $provinceId): object | null
    {
        try {
            return $this->wardsRepository->getWardsByProvinceId($provinceId);
        } catch (\Exception $e) {
            Log::error(__('ward.messages.get_wards_by_province_id_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
