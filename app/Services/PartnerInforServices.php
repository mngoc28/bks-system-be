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
                'company_name' => $request->company_name,
                'phone'        => $request->phone,
                'address'      => $request->address,
                'website'      => $request->website,
                'description'  => $request->description,
                'updated_by'   => Auth::id(),
            ];

            $upload = new UploadFile();
            $imageFields = ['image_1', 'image_2', 'image_3'];
            foreach ($imageFields as $field) {
                $avatarUrl = null;
                if ($request->hasFile($field)) {
                    //upload picture
                    $avatarUrl = $upload->uploadImage(
                        $request->file($field),
                        'partner',
                        $id,
                    );
                    $imageJustUploaded[] = $avatarUrl;
                    $oldAvatarUrl[] = $partner->$field;
                } elseif (!$request->$field) {
                    //delete picture
                    $avatarUrl = null;
                    $oldAvatarUrl = $partner->$field;
                }

                $dataUpdate[$field] = $avatarUrl;
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
}
