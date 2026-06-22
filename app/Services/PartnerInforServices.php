<?php

namespace App\Services;

use App\Helpers\UploadFile;
use App\Repositories\PartnerInforRepository\PartnerInforRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartnerInforServices
{
    protected $partnerInforRepository;

    /**
     * constructor method
     *
     * @param PartnerInforRepositoryInterface
     */
    public function __construct(PartnerInforRepositoryInterface $partnerInforRepository)
    {
        $this->partnerInforRepository = $partnerInforRepository;
    }

    /**
     * Get list partner information
     *
     * @param Request $request
     * @return array
     */
    public function getListPartnerInfor(Request $request): array
    {
        try {
            $partner = $this->partnerInforRepository->getListPartner($request);
            if (!$partner) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("partner.messages.get_list_failed"),
                ];
            }

            return [
                "success" => true,
                "data" => $partner,
                "message" => __("partner.messages.get_list_success")
            ];
        } catch (Exception $e) {
            Log::error(__("partner.messages.get_list_error"), [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("partner.messages.get_list_error"),
            ];
        }
    }

    /**
     * Get partner information by id
     *
     * @param int $id
     * @return array
     */
    public function getPartnerInforDetail(int $id): array
    {
        try {
            $partner = $this->partnerInforRepository->getPartnerById($id);
            if (!$partner) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("partner.messages.not_found"),
                ];
            }
            return [
                "success" => true,
                "data" => $partner,
                "message" => __("partner.messages.get_detail_success")
            ];
        } catch (Exception $e) {
            Log::error(__("partner.messages.find_error"), [
                "partnerInfo_id" => $id,
                "error" => $e->getMessage(),
            ]);
            return [
                "success" => false,
                "data" => null,
                "message" => __("partner.messages.find_error"),
            ];
        }
    }

    /**
     * Update partner information By ID
     *
     * @param int $id
     * @param Request $request
     * @return array
     */
    public function updatePartnerInfor(Request $request, $id): array
    {
        DB::beginTransaction();
        $imageJustUploaded = [];
        $oldAvatarUrl = [];
        try {
            $partner = $this->partnerInforRepository->find($id);
            if (!$partner) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("partner.messages.not_found"),
                ];
            }

            $dataUpdate = [
                'updated_by' => Auth::id(),
            ];

            $optionalFields = ['company_name', 'phone', 'address', 'website', 'description', 'province_id', 'ward_id'];
            foreach ($optionalFields as $field) {
                if ($request->has($field)) {
                    $dataUpdate[$field] = $request->input($field);
                }
            }

            $upload = new UploadFile();
            $imageFields = ['image_1', 'image_2', 'image_3'];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $avatarUrl = $upload->uploadImage(
                        $request->file($field),
                        'partner',
                        $id,
                    );
                    if (!$avatarUrl) {
                        throw new Exception('Partner image upload failed');
                    }
                    $imageJustUploaded[] = $avatarUrl;
                    if ($partner->$field) {
                        $oldAvatarUrl[] = $partner->$field;
                    }
                    $dataUpdate[$field] = $avatarUrl;
                } elseif ($request->get($field) === 'delete') {
                    // delete picture
                    if ($partner->$field) {
                        $oldAvatarUrl[] = $partner->$field;
                    }
                    $dataUpdate[$field] = null;
                }
            }

            $this->partnerInforRepository->update($id, $dataUpdate);
            $partner = $this->partnerInforRepository->getPartnerById($id);

            DB::commit();
            if (!empty($oldAvatarUrl)) {
                foreach ($oldAvatarUrl as $imagePath) {
                    $upload->deleteOldImage($imagePath);
                }
            }

            return [
                "success" => true,
                "data" => $partner,
                "message" => __("partner.messages.get_update_success")
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating partner: ' . $e->getMessage());
            if (!empty($imageJustUploaded)) {
                foreach ($imageJustUploaded as $imagePath) {
                    $upload->deleteOldImage($imagePath);
                }
            }

            return [
                "success" => false,
                "data" => null,
                "message" => __("partner.messages.update_error"),
            ];
        }
    }


    // ====== The functions below are APIs for the end user ======
    /**
     * Get random partners for homepage
     *
     * @param Request $request
     * @return array
     */
    public function getRandomPartners(Request $request): array
    {
        try {
            $partners = $this->partnerInforRepository->getRandomPartners($request);

            return [
                'success' => true,
                'data' => $partners,
                'message' => __('partner.messages.get_list_success')
            ];
        } catch (Exception $e) {
            Log::error(__('partner.messages.get_list_error'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partner.messages.get_list_error'),
            ];
        }
    }


    /**
     * ============================================
     * COMMON API
     * ============================================
     */
    /**
     * Get partners by province ID
     *
     * @param int $provinceId
     * @return array
     */
    public function handleGetPartnersByProvinceId(int $provinceId): array
    {
        try {
            $partners = $this->partnerInforRepository->getPartnersByProvinceId($provinceId);

            return [
                'success' => true,
                'data' => $partners,
                'message' => __('partner.messages.get_list_success')
            ];
        } catch (Exception $e) {
            Log::error(__('partner.messages.get_list_error'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partner.messages.get_list_error'),
            ];
        }
    }

    /**
     * Get partner detail by ID
     *
     * @param int $id
     * @return array
     */
    public function handlePartnerDetail(int $id): array
    {
        try {
            $results = $this->partnerInforRepository->getPartnerDetail($id);
            if (!$results) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('partner.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $results,
                'message' => __('partner.messages.get_detail_success')
            ];
        } catch (Exception $e) {
            Log::error(__('partner.messages.find_error'), [
                'partnerInfo_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partner.messages.find_error'),
            ];
        }
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get profile for partner
     *
     * @return array
     */
    public function handleGetProfileForPartner(): array
    {
        try {
            $userId = Auth::id();
            $partner = $this->partnerInforRepository->getPartnerByUserId($userId);
            if (!$partner) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("partner.messages.not_found"),
                ];
            }
            return [
                "success" => true,
                "data" => $partner,
                "message" => __("partner.messages.get_detail_success")
            ];
        } catch (Exception $e) {
            Log::error("Partner get profile failed: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("partner.messages.find_error"),
            ];
        }
    }

    /**
     * Update profile for partner
     *
     * @param Request $request
     * @return array
     */
    public function handleUpdateProfileForPartner(Request $request): array
    {
        try {
            $userId = Auth::id();
            $partner = $this->partnerInforRepository->getPartnerByUserId($userId);
            if (!$partner) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("partner.messages.not_found"),
                ];
            }
            return $this->updatePartnerInfor($request, $partner->id);
        } catch (Exception $e) {
            Log::error("Partner update profile failed: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("partner.messages.update_error"),
            ];
        }
    }
}
