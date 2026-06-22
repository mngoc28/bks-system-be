<?php

declare(strict_types=1);

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use App\Rules\CanDeletePropertyRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Property (listing) request validation — search, CRUD, images, bookings by property.
 */
final class PropertiesValidation
{
    /**
     * Validate search property request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function searchPropertyValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'partner_id'    => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'name'          => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'keyword'       => [
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
                'property_type_id' => [
                    'nullable',
                    'integer',
                    'exists:property_types,id',
                ],
                'rent_category'    => [
                    'nullable',
                    'integer',
                    'in:1,2,3',
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
                'id'            => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'with_rooms'    => [
                    'nullable',
                ],
                'rooms_limit'   => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:20',
                ],
                'include'       => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'occupancy_filter' => [
                    'nullable',
                    'string',
                    'in:vacant,occupied,maintenance',
                ],
                'min_rating'    => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:5',
                ],
                'has_rooms'     => [
                    'nullable',
                    'integer',
                    'in:0,1',
                ],
            ],
            [
                'name.max'          => __('property.validation.name.max'),
                'keyword.max'       => __('property.validation.name.max'),
                'ward_name.max'     => __('property.validation.ward_name.max'),
                'province_name.max' => __('property.validation.province_name.max'),
                'year_built.integer' => __('property.validation.year_built.integer'),
                'year_built.min'     => __('property.validation.year_built.min'),
                'property_type_id.integer' => __('property.validation.property_type_id.integer'),
                'property_type_id.exists'  => __('property.validation.property_type_id.exists'),
                'rent_category.integer'    => __('property.validation.rent_category.integer'),
                'rent_category.in'         => __('property.validation.rent_category.in'),
                'area_min.numeric'  => __('property.validation.area.numeric'),
                'area_min.min'      => __('property.validation.area.min'),
                'area_max.numeric'  => __('property.validation.area.numeric'),
                'area_max.min'      => __('property.validation.area.min'),
                'page.integer'      => __('pagination.page.integer'),
                'page.min'          => __('pagination.page.min'),
                'per_page.integer'  => __('pagination.per_page.integer'),
                'per_page.min'      => __('pagination.per_page.min'),
                'occupancy_filter.in' => __('property.validation.occupancy_filter.in'),
                'min_rating.numeric'  => __('property.validation.min_rating.numeric'),
                'min_rating.min'      => __('property.validation.min_rating.min'),
                'min_rating.max'      => __('property.validation.min_rating.max'),
                'has_rooms.integer'   => __('property.validation.has_rooms.integer'),
                'has_rooms.in'        => __('property.validation.has_rooms.in'),
            ],
            [
                'name'          => __('property.attributes.name'),
                'keyword'       => __('property.attributes.keyword'),
                'ward_name'     => __('property.attributes.ward_name'),
                'province_name' => __('property.attributes.province_name'),
                'year_built'    => __('property.attributes.year_built'),
                'property_type_id' => __('property.attributes.property_type_id'),
                'rent_category'    => __('property.attributes.rent_category'),
                'area_min'      => __('property.attributes.area'),
                'area_max'      => __('property.attributes.area'),
                'occupancy_filter' => __('property.attributes.occupancy_filter'),
                'min_rating'    => __('property.attributes.min_rating'),
                'has_rooms'     => __('property.attributes.has_rooms_filter'),
            ]
        );
    }

    /**
     * Validate create property request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function createPropertyValidation(Request $request)
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
                'property_type_id' => [
                    'required',
                    'integer',
                    'exists:property_types,id',
                ],
                'rent_category'    => [
                    'required',
                    'integer',
                    'in:1,2,3',
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
                'user_id.required'     => __('property.validation.user_id.required'),
                'user_id.integer'      => __('property.validation.user_id.integer'),
                'user_id.exists'       => __('property.validation.user_id.exists'),
                'province_id.required' => __('property.validation.province_id.required'),
                'province_id.integer'  => __('property.validation.province_id.integer'),
                'province_id.exists'   => __('property.validation.province_id.exists'),
                'ward_id.required'      => __('property.validation.ward_id.required'),
                'ward_id.integer'       => __('property.validation.ward_id.integer'),
                'ward_id.exists'       => __('property.validation.ward_id.exists'),
                'name.required'        => __('property.validation.name.required'),
                'name.max'             => __('property.validation.name.max'),
                'address_detail.max'   => __('property.validation.address_detail.max'),
                'number_of_floors.integer' => __('property.validation.number_of_floors.integer'),
                'number_of_floors.min'     => __('property.validation.number_of_floors.min'),
                'number_of_units.integer'  => __('property.validation.number_of_units.integer'),
                'number_of_units.min'      => __('property.validation.number_of_units.min'),
                'year_built.integer'       => __('property.validation.year_built.integer'),
                'year_built.min'           => __('property.validation.year_built.min'),
                'year_built.max'           => __('property.validation.year_built.max'),
                'property_type_id.required' => __('property.validation.property_type_id.required'),
                'property_type_id.integer'  => __('property.validation.property_type_id.integer'),
                'property_type_id.exists'   => __('property.validation.property_type_id.exists'),
                'rent_category.required'    => __('property.validation.rent_category.required'),
                'rent_category.integer'     => __('property.validation.rent_category.integer'),
                'rent_category.in'          => __('property.validation.rent_category.in'),
                'area.numeric'             => __('property.validation.area.numeric'),
                'area.min'                 => __('property.validation.area.min'),
                'created_by.exists'       => __('property.validation.created_by.exists'),
                'updated_by.exists'       => __('property.validation.updated_by.exists'),
            ],
            [
                'user_id'          => __('property.attributes.user_id'),
                'province_id'      => __('property.attributes.province_id'),
                'ward_id'          => __('property.attributes.ward_id'),
                'name'             => __('property.attributes.name'),
                'address_detail'   => __('property.attributes.address_detail'),
                'number_of_floors' => __('property.attributes.number_of_floors'),
                'number_of_units'  => __('property.attributes.number_of_units'),
                'year_built'       => __('property.attributes.year_built'),
                'property_type_id' => __('property.attributes.property_type_id'),
                'rent_category'    => __('property.attributes.rent_category'),
                'area'             => __('property.attributes.area'),
                'description'      => __('property.attributes.description'),
                'created_by'       => __('property.attributes.created_by'),
                'updated_by'       => __('property.attributes.updated_by'),
            ]
        );
    }

    /**
     * Validate detail property request
     *
     * @param int $id Property primary key
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function detailPropertyValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:properties,id',
                ],
            ],
            [
                'id.required' => __('property.validation.id.required'),
                'id.integer'  => __('property.validation.id.integer'),
                'id.exists'   => __('property.validation.id.exists'),
            ],
            [
                'id' => __('property.attributes.id'),
            ]
        );
    }

    /**
     * Validate partner property room preview request.
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function roomPreviewValidation(Request $request, int $id)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id'    => [
                    'required',
                    'integer',
                    'exists:properties,id',
                ],
                'limit' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:20',
                ],
            ],
            [
                'id.required' => __('property.validation.id.required'),
                'id.integer'  => __('property.validation.id.integer'),
                'id.exists'   => __('property.validation.id.exists'),
                'limit.integer' => __('pagination.per_page.integer'),
                'limit.min'     => __('pagination.per_page.min'),
                'limit.max'     => __('pagination.per_page.max'),
            ],
            [
                'id'    => __('property.attributes.id'),
                'limit' => __('pagination.per_page'),
            ]
        );
    }

    /**
     * Validate update property request
     *
     * @param Request $request
     * @param int $propertyId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function updatePropertyValidation(Request $request, int $propertyId)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $propertyId]),
            [
                'id'               => [
                    'required',
                    'integer',
                    'exists:properties,id',
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
                'property_type_id' => [
                    'nullable',
                    'integer',
                    'exists:property_types,id',
                ],
                'rent_category'    => [
                    'nullable',
                    'integer',
                    'in:1,2,3',
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
                'id.required'          => __('property.validation.id.required'),
                'id.integer'           => __('property.validation.id.integer'),
                'id.exists'            => __('property.validation.id.exists'),
                'user_id.integer'      => __('property.validation.user_id.integer'),
                'user_id.exists'       => __('property.validation.user_id.exists'),
                'province_id.integer'  => __('property.validation.province_id.integer'),
                'province_id.exists'   => __('property.validation.province_id.exists'),
                'ward_id.integer'      => __('property.validation.ward_id.integer'),
                'ward_id.exists'       => __('property.validation.ward_id.exists'),
                'name.max'             => __('property.validation.name.max'),
                'address_detail.max'   => __('property.validation.address_detail.max'),
                'number_of_floors.integer' => __('property.validation.number_of_floors.integer'),
                'number_of_floors.min'     => __('property.validation.number_of_floors.min'),
                'number_of_units.integer'  => __('property.validation.number_of_units.integer'),
                'number_of_units.min'      => __('property.validation.number_of_units.min'),
                'year_built.integer'       => __('property.validation.year_built.integer'),
                'year_built.min'           => __('property.validation.year_built.min'),
                'year_built.max'           => __('property.validation.year_built.max'),
                'property_type_id.integer' => __('property.validation.property_type_id.integer'),
                'property_type_id.exists'  => __('property.validation.property_type_id.exists'),
                'rent_category.integer'    => __('property.validation.rent_category.integer'),
                'rent_category.in'         => __('property.validation.rent_category.in'),
                'area.numeric'             => __('property.validation.area.numeric'),
                'area.min'                 => __('property.validation.area.min'),
                'created_by.exists'       => __('property.validation.created_by.exists'),
                'updated_by.exists'       => __('property.validation.updated_by.exists'),
            ],
            [
                'id'               => __('property.attributes.id'),
                'user_id'          => __('property.attributes.user_id'),
                'province_id'      => __('property.attributes.province_id'),
                'ward_id'          => __('property.attributes.ward_id'),
                'name'             => __('property.attributes.name'),
                'address_detail'   => __('property.attributes.address_detail'),
                'number_of_floors' => __('property.attributes.number_of_floors'),
                'number_of_units'  => __('property.attributes.number_of_units'),
                'year_built'       => __('property.attributes.year_built'),
                'property_type_id' => __('property.attributes.property_type_id'),
                'rent_category'    => __('property.attributes.rent_category'),
                'area'             => __('property.attributes.area'),
                'description'      => __('property.attributes.description'),
                'created_by'       => __('property.attributes.created_by'),
                'updated_by'       => __('property.attributes.updated_by'),
            ]
        );
    }

    /**
     * Validate delete property request
     *
     * @param int $propertyId Property primary key
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function deletePropertyValidation(int $propertyId)
    {
        return Validator::make(
            ['id' => $propertyId],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:properties,id',
                    new CanDeletePropertyRule(),
                ],
            ],
            [
                'id.required' => __('property.validation.id.required'),
                'id.integer'  => __('property.validation.id.integer'),
                'id.exists'   => __('property.validation.id.exists'),
            ],
            [
                'id' => __('property.attributes.id'),
            ]
        );
    }

    /**
     * Validate get bookings by property request
     *
     * @param int $propertyId Property primary key
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function getBookingsByPropertyValidation(int $propertyId, Request $request)
    {
        return Validator::make(
            array_merge(['id' => $propertyId], $request->all()),
            [
                'id'         => [
                    'required',
                    'integer',
                    'exists:properties,id',
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
                'id.required' => __('property.validation.id.required'),
                'id.integer' => __('property.validation.id.integer'),
                'id.exists' => __('property.validation.id.exists'),
                'start_date.date' => __('booking.validation.start_date.date'),
                'end_date.date' => __('booking.validation.end_date.date'),
                'end_date.after_or_equal' => __('booking.validation.end_date.after_or_equal'),
                'status.string' => __('booking.validation.status.string'),
                'status.in' => __('booking.validation.status.in'),
            ]
        );
    }
}
