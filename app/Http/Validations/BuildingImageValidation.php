<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class BuildingImageValidation
{
    /**
     * Validate get images by building ID request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function getByBuildingIdValidation(int $buildingId)
    {
        return Validator::make(
            ['building_id' => $buildingId],
            [
                'building_id' => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                ],
            ],
            [
                'building_id.required' => __('building_image.validation.building_id.required'),
                'building_id.integer' => __('building_image.validation.building_id.integer'),
                'building_id.exists' => __('building_image.validation.building_id.exists'),
            ]
        );
    }

    /**
     * Validate show building image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function showValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:building_images,id',
                ],
            ],
            [
                'id.required' => __('building_image.validation.id.required'),
                'id.integer' => __('building_image.validation.id.integer'),
                'id.exists' => __('building_image.validation.id.exists'),
            ]
        );
    }

    /**
     * Validate store building image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'building_id' => [
                    'required',
                    'integer',
                    'exists:buildings,id',
                ],
                'image_url' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'id_image_cloudinary' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'image_type' => [
                    'required',
                    'integer',
                ],
            ],
            [
                'building_id.required' => __('building_image.validation.building_id.required'),
                'building_id.integer' => __('building_image.validation.building_id.integer'),
                'building_id.exists' => __('building_image.validation.building_id.exists'),
                'image_url.required' => __('building_image.validation.image_url.required'),
                'image_url.string' => __('building_image.validation.image_url.string'),
                'image_url.max' => __('building_image.validation.image_url.max'),
                'id_image_cloudinary.required' => __('building_image.validation.id_image_cloudinary.required'),
                'id_image_cloudinary.string' => __('building_image.validation.id_image_cloudinary.string'),
                'id_image_cloudinary.max' => __('building_image.validation.id_image_cloudinary.max'),
                'image_type.required' => __('building_image.validation.image_type.required'),
                'image_type.integer' => __('building_image.validation.image_type.integer'),
            ]
        );
    }

    /**
     * Validate update building image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function updateValidation(Request $request, int $id)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:building_images,id',
                ],
                'image_url' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'id_image_cloudinary' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'image_type' => [
                    'required',
                    'integer',
                ],
            ],
            [
                'id.required' => __('building_image.validation.id.required'),
                'id.integer' => __('building_image.validation.id.integer'),
                'id.exists' => __('building_image.validation.id.exists'),
                'image_url.required' => __('building_image.validation.image_url.required'),
                'image_url.string' => __('building_image.validation.image_url.string'),
                'image_url.max' => __('building_image.validation.image_url.max'),
                'image_url.url' => __('building_image.validation.image_url.url'),
                'id_image_cloudinary.required' => __('building_image.validation.id_image_cloudinary.required'),
                'id_image_cloudinary.string' => __('building_image.validation.id_image_cloudinary.string'),
                'id_image_cloudinary.max' => __('building_image.validation.id_image_cloudinary.max'),
                'image_type.required' => __('building_image.validation.image_type.required'),
                'image_type.integer' => __('building_image.validation.image_type.integer'),
                'image_type.in' => __('building_image.validation.image_type.in'),
            ]
        );
    }

    /**
     * Validate destroy building image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function destroyValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:building_images,id',
                ],
            ],
            [
                'id.required' => __('building_image.validation.id.required'),
                'id.integer' => __('building_image.validation.id.integer'),
                'id.exists' => __('building_image.validation.id.exists'),
            ]
        );
    }

    /**
     * Validate sort building images request
     *
     * @param Request $request
     * @param int $buildingId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function sortValidation(Request $request, int $buildingId): \Illuminate\Validation\Validator
    {
        return Validator::make(array_merge($request->all(), ['building_id' => $buildingId]), [
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:building_images,id'],
        ], [
            'building_id.required' => __('building_image.validation.building_id.required'),
            'building_id.integer' => __('building_image.validation.building_id.integer'),
            'building_id.exists' => __('building_image.validation.building_id.exists'),
            'ids.required'   => __('building_image.validation.ids.required'),
            'ids.array'      => __('building_image.validation.ids.array'),
            'ids.*.integer'  => __('building_image.validation.ids.integer'),
            'ids.*.distinct' => __('building_image.validation.ids.distinct'),
            'ids.*.exists'   => __('building_image.validation.ids.exists'),
        ]);
    }
}
