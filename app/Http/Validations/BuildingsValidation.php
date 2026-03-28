<?php

declare(strict_types=1);

namespace App\Http\Validations;

use App\Enums\BuildingType;
use App\Exceptions\BusinessException;
use App\Models\Building;
use App\Rules\CanDeleteBuildingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Buildings Validation
 *
 * Handles validation for building-related operations
 */
final class BuildingsValidation
{
    /**
     * Validate search building request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function searchBuildingValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'name'          => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'ward_name'     => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'province_name' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'year_built'    => [
                    'nullable',
                    'integer',
                    'min:1900',
                ],
                'building_type' => [
                    'nullable',
                    'integer',
                    'in:' . implode(',', array_keys(BuildingType::labels())),
                ],
                'area_min'      => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'area_max'      => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'page'          => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'per_page'      => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
            ],
            [
                'name.max'          => __('building.validation.name.max'),
                'ward_name.max'     => __('building.validation.ward_name.max'),
                'province_name.max' => __('building.validation.province_name.max'),
                'year_built.integer' => __('building.validation.year_built.integer'),
                'year_built.min'     => __('building.validation.year_built.min'),
                'building_type.integer' => __('building.validation.building_type.integer'),
                'building_type.in'       => __('building.validation.building_type.in'),
                'area_min.numeric'  => __('building.validation.area.numeric'),
                'area_min.min'      => __('building.validation.area.min'),
                'area_max.numeric'  => __('building.validation.area.numeric'),
                'area_max.min'      => __('building.validation.area.min'),
                'page.integer'      => __('pagination.page.integer'),
                'page.min'          => __('pagination.page.min'),
                'per_page.integer'  => __('pagination.per_page.integer'),
                'per_page.min'      => __('pagination.per_page.min'),
            ],
            [
                'name'          => __('building.attributes.name'),
                'ward_name'     => __('building.attributes.ward_name'),
                'province_name' => __('building.attributes.province_name'),
                'year_built'    => __('building.attributes.year_built'),
                'building_type' => __('building.attributes.building_type'),
                'area_min'      => __('building.attributes.area'),
                'area_max'      => __('building.attributes.area'),
            ]
        );
    }

    /**
     * Validate create building request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function createBuildingValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_id'          => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'province_id'      => [
                    'required',
                    'integer',
                    'exists:provinces,id',
                ],
                'ward_id'          => [
                    'required',
                    'integer',
                    'exists:wards,id',
                ],
                'name'             => [
                    'required',
                    'string',
                    'max:255',
                ],
                'address_detail'   => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'number_of_floors' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'number_of_units'  => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'year_built'       => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 10),
                ],
                'building_type'   => [
                    'nullable',
                    'integer',
                    'in:' . implode(',', array_keys(BuildingType::labels())),
                ],
                'area'             => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'description'      => [
                    'nullable',
                    'string',
                ],
                'created_by'       => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'updated_by'       => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
            ],
            [
                'user_id.required'     => __('building.validation.user_id.required'),
                'user_id.integer'      => __('building.validation.user_id.integer'),
                'user_id.exists'       => __('building.validation.user_id.exists'),
                'province_id.required' => __('building.validation.province_id.required'),
                'province_id.integer'  => __('building.validation.province_id.integer'),
                'province_id.exists'   => __('building.validation.province_id.exists'),
                'ward_id.required'      => __('building.validation.ward_id.required'),
                'ward_id.integer'       => __('building.validation.ward_id.integer'),
                'ward_id.exists'       => __('building.validation.ward_id.exists'),
                'name.required'        => __('building.validation.name.required'),
                'name.max'             => __('building.validation.name.max'),
                'address_detail.max'   => __('building.validation.address_detail.max'),
                'number_of_floors.integer' => __('building.validation.number_of_floors.integer'),
                'number_of_floors.min'     => __('building.validation.number_of_floors.min'),
                'number_of_units.integer'  => __('building.validation.number_of_units.integer'),
                'number_of_units.min'      => __('building.validation.number_of_units.min'),
                'year_built.integer'       => __('building.validation.year_built.integer'),
                'year_built.min'           => __('building.validation.year_built.min'),
                'year_built.max'           => __('building.validation.year_built.max'),
                'building_type.integer'    => __('building.validation.building_type.integer'),
                'building_type.in'        => __('building.validation.building_type.in'),
                'area.numeric'             => __('building.validation.area.numeric'),
                'area.min'                 => __('building.validation.area.min'),
                'created_by.exists'       => __('building.validation.created_by.exists'),
                'updated_by.exists'       => __('building.validation.updated_by.exists'),
            ],
            [
                'user_id'          => __('building.attributes.user_id'),
                'province_id'      => __('building.attributes.province_id'),
                'ward_id'          => __('building.attributes.ward_id'),
                'name'             => __('building.attributes.name'),
                'address_detail'   => __('building.attributes.address_detail'),
                'number_of_floors' => __('building.attributes.number_of_floors'),
                'number_of_units'  => __('building.attributes.number_of_units'),
                'year_built'       => __('building.attributes.year_built'),
                'building_type'    => __('building.attributes.building_type'),
                'area'             => __('building.attributes.area'),
                'description'      => __('building.attributes.description'),
                'created_by'       => __('building.attributes.created_by'),
                'updated_by'       => __('building.attributes.updated_by'),
            ]
        );
    }

    /**
     * Validate detail building request
     *
     * @param int $buildingId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function detailBuildingValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                ],
            ],
            [
                'id.required' => __('building.validation.id.required'),
                'id.integer'  => __('building.validation.id.integer'),
                'id.exists'   => __('building.validation.id.exists'),
            ],
            [
                'id' => __('building.attributes.id'),
            ]
        );
    }

    /**
     * Validate update building request
     *
     * @param Request $request
     * @param int $buildingId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function updateBuildingValidation(Request $request, int $buildingId)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $buildingId]),
            [
                'id'               => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                ],
                'user_id'          => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'province_id'      => [
                    'nullable',
                    'integer',
                    'exists:provinces,id',
                ],
                'ward_id'          => [
                    'nullable',
                    'integer',
                    'exists:wards,id',
                ],
                'name'             => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'address_detail'   => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'number_of_floors' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'number_of_units'  => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'year_built'       => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 10),
                ],
                'building_type'    => [
                    'nullable',
                    'integer',
                    'in:' . implode(',', array_keys(BuildingType::labels())),
                ],
                'area'             => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'description'      => [
                    'nullable',
                    'string',
                ],
                'created_by'       => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'updated_by'       => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
            ],
            [
                'id.required'          => __('building.validation.id.required'),
                'id.integer'           => __('building.validation.id.integer'),
                'id.exists'            => __('building.validation.id.exists'),
                'user_id.integer'      => __('building.validation.user_id.integer'),
                'user_id.exists'       => __('building.validation.user_id.exists'),
                'province_id.integer'  => __('building.validation.province_id.integer'),
                'province_id.exists'   => __('building.validation.province_id.exists'),
                'ward_id.integer'      => __('building.validation.ward_id.integer'),
                'ward_id.exists'       => __('building.validation.ward_id.exists'),
                'name.max'             => __('building.validation.name.max'),
                'address_detail.max'   => __('building.validation.address_detail.max'),
                'number_of_floors.integer' => __('building.validation.number_of_floors.integer'),
                'number_of_floors.min'     => __('building.validation.number_of_floors.min'),
                'number_of_units.integer'  => __('building.validation.number_of_units.integer'),
                'number_of_units.min'      => __('building.validation.number_of_units.min'),
                'year_built.integer'       => __('building.validation.year_built.integer'),
                'year_built.min'           => __('building.validation.year_built.min'),
                'year_built.max'           => __('building.validation.year_built.max'),
                'building_type.integer'    => __('building.validation.building_type.integer'),
                'building_type.in'         => __('building.validation.building_type.in'),
                'area.numeric'             => __('building.validation.area.numeric'),
                'area.min'                 => __('building.validation.area.min'),
                'created_by.exists'       => __('building.validation.created_by.exists'),
                'updated_by.exists'       => __('building.validation.updated_by.exists'),
            ],
            [
                'id'               => __('building.attributes.id'),
                'user_id'          => __('building.attributes.user_id'),
                'province_id'      => __('building.attributes.province_id'),
                'ward_id'          => __('building.attributes.ward_id'),
                'name'             => __('building.attributes.name'),
                'address_detail'   => __('building.attributes.address_detail'),
                'number_of_floors' => __('building.attributes.number_of_floors'),
                'number_of_units'  => __('building.attributes.number_of_units'),
                'year_built'       => __('building.attributes.year_built'),
                'building_type'    => __('building.attributes.building_type'),
                'area'             => __('building.attributes.area'),
                'description'      => __('building.attributes.description'),
                'created_by'       => __('building.attributes.created_by'),
                'updated_by'       => __('building.attributes.updated_by'),
            ]
        );
    }

    /**
     * Validate delete building request
     *
     * @param int $buildingId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function deleteBuildingValidation(int $buildingId)
    {
        return Validator::make(
            ['id' => $buildingId],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                    new CanDeleteBuildingRule(),
                ],
            ],
            [
                'id.required' => __('building.validation.id.required'),
                'id.integer'  => __('building.validation.id.integer'),
                'id.exists'   => __('building.validation.id.exists'),
            ],
            [
                'id' => __('building.attributes.id'),
            ]
        );
    }

    /**
     * Validate get bookings by building request
     *
     * @param int $buildingId
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function getBookingsByBuildingValidation(int $buildingId, Request $request)
    {
        return Validator::make(
            array_merge(['id' => $buildingId], $request->all()),
            [
                'id'         => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                ],
                'start_date' => [
                    'nullable',
                    'date',
                ],
                'end_date'   => [
                    'nullable',
                    'date',
                    'after_or_equal:start_date',
                ],
                'status'     => [
                    'nullable',
                    'string',
                    'in:pending,confirmed,completed,cancelled',
                ],
                'page'       => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'per_page'   => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
            ],
            [
                'id.required' => __('building.validation.id.required'),
                'id.integer' => __('building.validation.id.integer'),
                'id.exists' => __('building.validation.id.exists'),
                'start_date.date' => __('booking.validation.start_date.date'),
                'end_date.date' => __('booking.validation.end_date.date'),
                'end_date.after_or_equal' => __('booking.validation.end_date.after_or_equal'),
                'status.string' => __('booking.validation.status.string'),
                'status.in' => __('booking.validation.status.in'),
            ]
        );
    }
}
