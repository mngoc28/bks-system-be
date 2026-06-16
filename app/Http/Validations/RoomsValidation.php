<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use Illuminate\Validation\Rule;

final class RoomsValidation
{
    /**
     * Validate search room request parameters.
     *
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public function searchRoomValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'property_id' => ['nullable', 'integer', 'exists:properties,id'],
                'property_ids' => ['nullable', 'array', 'max:100'],
                'property_ids.*' => ['integer', 'exists:properties,id'],
                'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
                'ward_id' => ['nullable', 'integer', 'exists:wards,id'],
                'partner_id' => ['nullable', 'integer', 'exists:users,id'],
                'property_type_id' => ['nullable', 'integer', 'exists:property_types,id'],
                'keyword' => ['nullable', 'string', 'max:255'],
                'title'       => ['nullable', 'string', 'max:100'],
                'room_number' => ['nullable', 'string', 'max:50'],
                'room_type'   => ['nullable', 'in:' . implode(',', RoomType::roomTypeValues())],
                'status'      => ['nullable', 'in:' . implode(',', RoomStatus::statusValues())],
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['nullable', 'in:cheapest_daily_price,people'],
                'sort_direction' => ['nullable', 'in:asc,desc'],
                'tourist_spot_slug' => ['nullable', 'string', 'max:255', 'exists:tourist_spots,slug'],
                'price_min' => ['nullable', 'numeric', 'min:0'],
                'price_max' => ['nullable', 'numeric', 'min:0'],
                'guests' => ['nullable', 'integer', 'min:1'],
                'rent_type' => ['nullable', 'string', 'in:daily,monthly'],
            ],
            [
                'property_id.exists' => __('room.validation.property_id.exists'),
                'property_ids.array' => __('room.validation.property_id.exists'),
                'property_ids.max' => __('room.validation.property_id.exists'),
                'property_ids.*.integer' => __('room.validation.property_id.integer'),
                'property_ids.*.exists' => __('room.validation.property_id.exists'),
                'title.string'        => __('room.validation.title.string'),
                'title.max'           => __('room.validation.title.max'),
                'room_number.string'  => __('room.validation.room_number.string'),
                'room_number.max'     => __('room.validation.room_number.max'),
                'room_type.in'        => __('room.validation.room_type.in'),
                'status.in'           => __('room.validation.status.in'),
            ],
            [
                'property_id' => __('room.attributes.property_id'),
                'property_ids' => __('room.attributes.property_id'),
                'property_ids.*' => __('room.attributes.property_id'),
                'province_id' => __('property.attributes.province_id'),
                'ward_id' => __('property.attributes.ward_id'),
                'partner_id' => 'đối tác',
                'property_type_id' => __('property.attributes.property_type_id'),
                'keyword' => __('room.attributes.title'),
                'title'       => __('room.attributes.title'),
                'room_number' => __('room.attributes.room_number'),
                'room_type'   => __('room.attributes.room_type'),
                'status'      => __('room.attributes.status'),
                'page' => 'trang',
                'per_page' => 'số lượng mỗi trang',
                'sort_by' => 'trường sắp xếp',
                'sort_direction' => 'chiều sắp xếp',
            ]
        );
    }

    /**
     * Validate public tourist spot suggestion list.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function publicTouristSpotsValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'keyword' => ['nullable', 'string', 'max:255'],
                'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
                // Query strings often send "1"/"0" or "true"/"false"; use $request->boolean() after validation.
                'featured_only' => ['nullable'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ],
        );
    }

    /**
     * Validate suggested rooms grouped by tourist spot for homepage.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function suggestedRoomsByTouristSpotValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'tourist_spot_ids' => ['nullable', 'array', 'max:20'],
                'tourist_spot_ids.*' => ['integer', 'exists:tourist_spots,id'],
                'tourist_spot_slugs' => ['nullable', 'array', 'max:20'],
                'tourist_spot_slugs.*' => ['string', 'max:255', 'exists:tourist_spots,slug'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
            ],
            [],
            [
                'tourist_spot_ids' => 'điểm du lịch',
                'tourist_spot_slugs' => 'điểm du lịch',
                'limit' => __('room.attributes.title'),
            ]
        );
    }

    /**
     * Validate suggested rooms by province request parameters.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function suggestedRoomsByProvinceValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'province_ids' => ['nullable', 'array', 'max:20'],
                'province_ids.*' => ['integer', 'exists:provinces,id'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ],
            [
                'province_ids.array' => __('room.validation.property_id.exists'),
                'province_ids.max' => __('room.validation.property_id.exists'),
                'province_ids.*.integer' => __('room.validation.property_id.integer'),
                'province_ids.*.exists' => __('room.validation.property_id.exists'),
                'limit.integer' => __('room.validation.title.string'),
                'limit.min' => __('room.validation.title.string'),
                'limit.max' => __('room.validation.title.string'),
            ],
            [
                'province_ids' => __('property.attributes.province_id'),
                'province_ids.*' => __('property.attributes.province_id'),
                'limit' => __('room.attributes.title'),
            ]
        );
    }

    /**
     * Validate room ID parameter.
     *
     * @param int $id The room ID to validate.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public function deleteRoomValidation($id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => ['required', 'integer', 'exists:rooms,id',
                Rule::unique('bookings', 'room_id')],
            ],
            [
                'id.required' => __('room.validation.id.required'),
                'id.integer'  => __('room.validation.id.integer'),
                'id.exists'   => __('room.validation.id.exists'),
                'id.unique'   => __('room.validation.id.unique'),
            ],
            [
                'id' => __('room.attributes.id'),
            ]
        );
    }

    /**
     * Validate room ID parameter.
     *
     * @param int $id The room ID to validate.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public function idValidation($id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => ['required', 'integer', 'exists:rooms,id'],
            ],
            [
                'id.required' => __('room.validation.id.required'),
                'id.exists'   => __('room.validation.id.exists'),
                'id.unique'   => __('room.validation.id.unique'),
            ],
            [
                'id' => __('room.attributes.id'),
            ]
        );
    }

    /**
     * Validate store room request parameters.
     *
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public function createRoomValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'property_id' => ['required', 'integer', 'exists:properties,id'],
                'title' => ['required', 'string', 'max:100'],
                'room_number' => ['required'],
                'deposit' => ['nullable', 'numeric', 'min:0'],
                'area'        => ['required', 'numeric', 'min:0'],
                'floor_number' => ['required', 'integer', 'min:0'],
                'people' => ['required', 'integer', 'min:1'],
                'bedrooms_count' => ['nullable', 'integer', 'min:1'],
                'beds_count' => ['nullable', 'integer', 'min:1'],
                'room_type' => ['required', 'in:' . implode(',', RoomType::roomTypeValues())],
                'status'      => ['required', 'in:' . implode(',', RoomStatus::statusValues())],
                'description' => ['nullable', 'string'],
                'amenities' => ['required', 'array', 'min:1'],
                'amenities.*' => ['required', 'integer', 'exists:amenities,id'],

                'services' => ['required', 'array', 'min:1'],
                'services.*' => ['required', 'integer', 'exists:services,id'],

                'prices' => ['required', 'array', 'min:1'],
                'prices.*.price_package_id' => ['required', 'integer', 'exists:price_packages,id', 'distinct'],
                'prices.*.unit' => ['required', 'string', 'in:day,week,month,year'],
                'prices.*.unit_price' => ['required', 'numeric', 'min:0'],
            ],
            [
                // Property
                'property_id.required' => __('room.validation.property_id.required'),
                'property_id.integer'  => __('room.validation.property_id.integer'),
                'property_id.exists'   => __('room.validation.property_id.exists'),

                // Title
                'title.required' => __('room.validation.title.required'),
                'title.string'   => __('room.validation.title.string'),
                'title.max'      => __('room.validation.title.max'),

                // Room Number
                'room_number.required' => __('room.validation.room_number.required'),

                // Deposit
                'deposit.numeric' => __('room.validation.deposit.numeric'),
                'deposit.min'     => __('room.validation.deposit.min'),

                // Area
                'area.required' => __('room.validation.area.required'),
                'area.numeric'  => __('room.validation.area.numeric'),
                'area.min'      => __('room.validation.area.min'),

                // Floor
                'floor_number.required' => __('room.validation.floor_number.required'),
                'floor_number.integer'  => __('room.validation.floor_number.integer'),
                'floor_number.min'      => __('room.validation.floor_number.min'),

                // People
                'people.required' => __('room.validation.people.required'),
                'people.integer'  => __('room.validation.people.integer'),
                'people.min'      => __('room.validation.people.min'),

                // Room Type
                'room_type.required' => __('room.validation.room_type.required'),
                'room_type.in'       => __('room.validation.room_type.in'),

                // Status
                'status.required' => __('room.validation.status.required'),
                'status.in'       => __('room.validation.status.in'),

                // Description
                'description.string' => __('room.validation.description.string'),

                // Amenities
                'amenities.required' => __('room.validation.amenities.required'),
                'amenities.array'    => __('room.validation.amenities.array'),
                'amenities.min'      => __('room.validation.amenities.min'),

                'amenities.*.required' => __('room.validation.amenities.*.required'),
                'amenities.*.integer'  => __('room.validation.amenities.*.integer'),
                'amenities.*.exists'   => __('room.validation.amenities.*.exists'),

                // Services
                'services.required' => __('room.validation.services.required'),
                'services.array'    => __('room.validation.services.array'),
                'services.min'      => __('room.validation.services.min'),

                'services.*.required' => __('room.validation.services.*.required'),
                'services.*.integer'  => __('room.validation.services.*.integer'),
                'services.*.exists'   => __('room.validation.services.*.exists'),

                // Prices
                'prices.*.price_package_id.required' => trans('room.validation.prices.price_package_id.required'),
                'prices.*.price_package_id.integer'  => trans('room.validation.prices.price_package_id.integer'),
                'prices.*.price_package_id.exists'   => trans('room.validation.prices.price_package_id.exists'),

                'prices.*.unit.required' => trans('room.validation.prices.unit.required'),
                'prices.*.unit.string'   => trans('room.validation.prices.unit.string'),
                'prices.*.unit.in'       => trans('room.validation.prices.unit.in'),

                'prices.*.unit_price.required' => trans('room.validation.prices.unit_price.required'),
                'prices.*.unit_price.numeric'  => trans('room.validation.prices.unit_price.numeric'),
                'prices.*.unit_price.min'      => trans('room.validation.prices.unit_price.min'),
            ],
            [
                'property_id' => __('room.attributes.property_id'),
                'title'       => __('room.attributes.title'),
                'room_number' => __('room.attributes.room_number'),
                'deposit'     => __('room.attributes.deposit'),
                'area'        => __('room.attributes.area'),
                'floor_number'  => __('room.attributes.floor_number'),
                'people'      => __('room.attributes.people'),
                'bedrooms_count' => 'số phòng ngủ',
                'beds_count' => 'số giường',
                'room_type'   => __('room.attributes.room_type'),
                'status'      => __('room.attributes.status'),
                'description' => __('room.attributes.description'),
                'images'      => __('room.attributes.images'),
                'images.*.image_url'  => __('room.attributes.images.*.image_url'),
                'images.*.image_type' => __('room.attributes.images.*.image_type'),
                'images.*.sort'       => __('room.attributes.images.*.sort'),
                'amenities'   => __('room.attributes.amenities'),
                'amenities.*' => __('room.attributes.amenities.*'),
                'services'    => __('room.attributes.services'),
                'services.*'  => __('room.attributes.services.*'),
                'prices'      => __('room.attributes.prices'),
                'prices.*.price_package_id' => __('room.attributes.prices.*.price_package_id'),
                'prices.*.unit'       => __('room.attributes.prices.*.unit'),
                'prices.*.price'      => __('room.attributes.prices.*.price'),
                'prices.*.unit_price'       => __('room.attributes.prices.*.unit_price'),
            ]
        );
    }

    /**
     * Validate update room request parameters.
     *
     * @param Request $request The incoming HTTP request.
     * @param int $id The room ID to validate.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public function updateRoomValidation(Request $request, $id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id'          => ['required', 'integer', 'exists:rooms,id'],
                'property_id' => ['sometimes', 'required', 'integer', 'exists:properties,id'],
                'title'       => ['sometimes', 'string', 'max:100'],
                'room_number' => ['sometimes', 'required', 'string', 'max:50'],
                'deposit'     => ['nullable', 'numeric', 'min:0'],
                'area'        => ['sometimes', 'required', 'numeric', 'min:0'],
                'floor_number'=> ['sometimes', 'required', 'integer', 'min:0'],
                'people'      => ['sometimes', 'required', 'integer', 'min:1'],
                'bedrooms_count' => ['nullable', 'integer', 'min:1'],
                'beds_count'  => ['nullable', 'integer', 'min:1'],
                'room_type'   => ['sometimes', 'required', 'in:' . implode(',', RoomType::roomTypeValues())],
                'status'      => ['sometimes', 'required', 'integer', 'in:' . implode(',', RoomStatus::statusValues())],
                'description' => ['nullable', 'string'],
                'images'      => ['nullable', 'array'],
                'images.*.image_url'  => ['nullable', 'string', 'max:255'],
                'images.*.image_type' => ['nullable', 'string', 'max:50'],
                'images.*.sort'       => ['nullable', 'integer', 'min:1'],
                'amenities'   => ['nullable', 'array', 'min:1'],
                'amenities.*' => ['nullable', 'integer', 'exists:amenities,id'],
                'services'    => ['nullable', 'array', 'min:1'],
                'services.*'  => ['nullable', 'integer', 'exists:services,id'],
                'prices'      => ['nullable', 'array', 'min:1'],
                'prices.*.price_package_id' => [
                    'nullable',
                    'integer',
                    'distinct',
                    'exists:price_packages,id',
                ],
                'prices.*.unit'       => ['nullable', 'string', 'in:day,week,month,year'],
                'prices.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'id.required'          => __('room.validation.id.required'),
                'id.integer'           => __('room.validation.id.integer'),
                'id.exists'            => __('room.validation.id.exists'),
                'property_id.exists'   => __('room.validation.property_id.exists'),
                'title.string'         => __('room.validation.title.string'),
                'title.max'            => __('room.validation.title.max'),
                'room_number.required' => __('room.validation.room_number.required'),
                'room_number.string'   => __('room.validation.room_number.string'),
                'room_number.max'      => __('room.validation.room_number.max'),
                'deposit.numeric'      => __('room.validation.deposit.numeric'),
                'deposit.min'          => __('room.validation.deposit.min'),
                'area.required'        => __('room.validation.area.required'),
                'area.numeric'         => __('room.validation.area.numeric'),
                'area.min'             => __('room.validation.area.min'),
                'floor_number.required'=> __('room.validation.floor_number.required'),
                'floor_number.integer' => __('room.validation.floor_number.integer'),
                'floor_number.min'     => __('room.validation.floor_number.min'),
                'people.required'      => __('room.validation.people.required'),
                'people.integer'       => __('room.validation.people.integer'),
                'people.min'           => __('room.validation.people.min'),
                'room_type.required'   => __('room.validation.room_type.required'),
                'room_type.in'         => __('room.validation.room_type.in'),
                'status.required'      => __('room.validation.status.required'),
                'status.integer'       => __('room.validation.status.integer'),
                'status.in'            => __('room.validation.status.in'),
                'description.string'   => __('room.validation.description.string'),
                'images.array'         => __('room.validation.images.array'),
                'images.*.image_url.string'  => __('room.validation.images.*.image_url.string'),
                'images.*.image_url.max'     => __('room.validation.images.*.image_url.max'),
                'images.*.image_type.string' => __('room.validation.images.*.image_type.string'),
                'images.*.image_type.max'    => __('room.validation.images.*.image_type.max'),
                'images.*.sort.integer'      => __('room.validation.images.*.sort.integer'),
                'images.*.sort.min'          => __('room.validation.images.*.sort.min'),
                'amenities.array'            => __('room.validation.amenities.array'),
                'amenities.min'              => __('room.validation.amenities.min'),
                'amenities.*.integer'        => __('room.validation.amenities.*.integer'),
                'amenities.*.exists'         => __('room.validation.amenities.*.exists'),
                'services.array'             => __('room.validation.services.array'),
                'services.min'               => __('room.validation.services.min'),
                'services.*.integer'         => __('room.validation.services.*.integer'),
                'services.*.exists'          => __('room.validation.services.*.exists'),
                'prices.array'               => __('room.validation.prices.array'),
                'prices.min'                 => __('room.validation.prices.min'),
                'prices.*.price_package_id.integer' => __('room.validation.prices.price_package_id.integer'),
                'prices.*.price_package_id.exists'  => __('room.validation.prices.price_package_id.exists'),
                'prices.*.unit.string'       => __('room.validation.prices.unit.string'),
                'prices.*.unit.in'           => __('room.validation.prices.unit.in'),
                'prices.*.unit_price.numeric'=> __('room.validation.prices.unit_price.numeric'),
                'prices.*.unit_price.min'    => __('room.validation.prices.unit_price.min'),
            ],
            [
                'property_id' => __('room.attributes.property_id'),
                'title'       => __('room.attributes.title'),
                'room_number' => __('room.attributes.room_number'),
                'deposit'     => __('room.attributes.deposit'),
                'area'        => __('room.attributes.area'),
                'floor_number'=> __('room.attributes.floor_number'),
                'people'      => __('room.attributes.people'),
                'bedrooms_count' => 'số phòng ngủ',
                'beds_count' => 'số giường',
                'room_type'   => __('room.attributes.room_type'),
                'status'      => __('room.attributes.status'),
                'description' => __('room.attributes.description'),
                'images'      => __('room.attributes.images'),
                'images.*.image_url'  => __('room.attributes.images.*.image_url'),
                'images.*.image_type' => __('room.attributes.images.*.image_type'),
                'images.*.sort'       => __('room.attributes.images.*.sort'),
                'amenities'   => __('room.attributes.amenities'),
                'amenities.*' => __('room.attributes.amenities.*'),
                'services'    => __('room.attributes.services'),
                'services.*'  => __('room.attributes.services.*'),
                'prices'      => __('room.attributes.prices'),
                'prices.*.price_package_id' => __('room.attributes.prices.*.price_package_id'),
                'prices.*.unit'       => __('room.attributes.prices.*.unit'),
                'prices.*.price'      => __('room.attributes.prices.*.price'),
                'prices.*.unit_price' => __('room.attributes.prices.*.unit_price'),
            ]
        );
    }
}
