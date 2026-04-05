<?php

declare(strict_types=1);

namespace App\Repositories\PartnerInforRepository;

use App\Enums\UserType;
use App\Models\PartnerInfo;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoomStatus;
use Illuminate\Support\Facades\DB;

class PartnerInforRepository extends BaseRepository implements PartnerInforRepositoryInterface
{
    /**
     * Get model class Name
     *
     * @return mixed
     */
    public function getModel(): mixed
    {
        return PartnerInfo::class;
    }

    /**
     * Get list Parter information
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getListPartner(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = $this->model->select([
            'partner_info.*',
            'users.name as user_name',
            'provinces.name as province_name',
            'wards.name as ward_name',
        ])
            ->leftjoin('users', 'partner_info.user_id', '=', 'users.id')
            ->leftjoin('provinces', 'partner_info.province_id', '=', 'provinces.id')
            ->leftjoin('wards', 'partner_info.ward_id', '=', 'wards.id');

        //where filter
        if ($user && $user->role !== UserType::ADMIN) {
            $query->where('partner_info.user_id', $user->id);
        }
        if ($request->filled('user_name')) {
            $query->where('users.name', 'LIKE', '%' . $request->user_name . '%');
        }
        if ($request->filled('province_name')) {
            $query->where('provinces.name', 'LIKE', '%' . $request->province_name . '%');
        }
        if ($request->filled('ward_name')) {
            $query->where('wards.name', 'LIKE', '%' . $request->ward_name . '%');
        }
        if ($request->filled("phone")) {
            $query->where('partner_info.phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('address')) {
            $query->where('partner_info.address', 'like', '%' . $request->address . '%');
        }
        //sort
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, ['id', 'user_name', 'province_name', 'ward_name', 'created_at', 'updated_at'])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('id', 'desc');
        }

        $perPage = (int) ($request->filled("per_page") ?
            $request->per_page : config("const.DEFAULT_PER_PAGE"));
        $page = (int) ($request->filled("page") ? $request->page : config("const.DEFAULT_PAGE"));

        return $query->paginate($perPage, ["*"], "page", $page);
    }

    /**
     * Get Partner By Id
     *
     * @param int $int
     * @return object
     */
    public function getPartnerById(int $id): ?object
    {
        $user = Auth::user();

        $query = $this->model
            ->select([
                'partner_info.*',
                'users.name as user_name',
                'provinces.name as province_name',
                'wards.name as ward_name',
            ])
            ->leftJoin('users', 'partner_info.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'partner_info.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'partner_info.ward_id', '=', 'wards.id')
            ->where('partner_info.id', $id);

        if ($user->role !== UserType::ADMIN) {
            $query->where('partner_info.user_id', $user->id);
        }

        return $query->first();
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
        $limit = $request->input('limit', config('const.DEFAULT_PER_PAGE'));
        return $this->model
            ->join('provinces as p', 'partner_info.province_id', '=', 'p.id')
            ->join('wards as w', 'partner_info.ward_id', '=', 'w.id')
            ->inRandomOrder()
            ->limit($limit)
            ->select(
                'partner_info.id',
                'partner_info.phone',
                'partner_info.address',
                'partner_info.website',
                'partner_info.image_1',
                'p.name as province_name',
                'w.name as ward_name'
            )
            ->get()
            ->toArray();
    }

    /**
     * Get partners by province ID
     *
     * @param int $provinceId
     * @return array
     */
    public function getPartnersByProvinceId(int $provinceId): array
    {
        return $this->model
            ->join('users as u', 'partner_info.user_id', '=', 'u.id')
            ->join('provinces as p', 'partner_info.province_id', '=', 'p.id')
            ->join('wards as w', 'partner_info.ward_id', '=', 'w.id')
            ->where('partner_info.province_id', $provinceId)
            ->select(
                'partner_info.id',
                'partner_info.province_id',
                'partner_info.company_name',
                'partner_info.phone',
                'partner_info.address',
                'partner_info.website',
                'partner_info.description',
                'u.email as user_email',
                'p.name as province_name',
                'w.name as ward_name'
            )
            ->get()
            ->toArray();
    }

    /**
     * Get public partner by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getPartnerDetail(int $id): ?object
    {
        $partner = $this->model
            ->join('provinces as p', 'partner_info.province_id', '=', 'p.id')
            ->join('wards as w', 'partner_info.ward_id', '=', 'w.id')
            ->join('users as u', 'partner_info.user_id', '=', 'u.id')
            ->where('partner_info.id', $id)
            ->first([
                'partner_info.image_1',
                'partner_info.image_2',
                'partner_info.image_3',
                'partner_info.company_name',
                'partner_info.description',
                'partner_info.phone',
                'partner_info.address',
                'partner_info.website',
                'p.name as province_name',
                'partner_info.province_id',
                'u.email as user_email',
            ]);

        if (!$partner) {
            return null;
        }

        return $partner;
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get partner information by associated User ID
     *
     * @param int $userId
     * @return object|null
     */
    public function getPartnerByUserId(int $userId): ?object
    {
        return $this->model
            ->select([
                'partner_info.*',
                'users.name as user_name',
                'provinces.name as province_name',
                'wards.name as ward_name',
            ])
            ->leftJoin('users', 'partner_info.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'partner_info.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'partner_info.ward_id', '=', 'wards.id')
            ->where('partner_info.user_id', $userId)
            ->first();
    }
}
