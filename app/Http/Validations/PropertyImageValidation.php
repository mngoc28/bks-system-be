<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class PropertyImageValidation
{
    /**
     * Validate get images by property ID request
     *
     * @param int $propertyId
     * @return \Illuminate\Validation\Validator
     */
    public function getByPropertyIdValidation(int $propertyId)
    {
        return Validator::make(
            ['property_id' => $propertyId],
            [
                'property_id' => [
                    'required',
                    'integer',
                    'exists:properties,id',
                ],
            ],
            [
                'property_id.required' => __('property_image.validation.property_id.required'),
                'property_id.integer' => __('property_image.validation.property_id.integer'),
                'property_id.exists' => __('property_image.validation.property_id.exists'),
            ]
        );
    }

    /**
     * Validate show property image request
     *
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function showValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:property_images,id',
                ],
            ],
            [
                'id.required' => __('property_image.validation.id.required'),
                'id.integer' => __('property_image.validation.id.integer'),
                'id.exists' => __('property_image.validation.id.exists'),
            ]
        );
    }

    /**
     * Validate store property image request
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'property_id' => [
                    'required',
                    'integer',
                    'exists:properties,id',
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
                'property_id.required' => __('property_image.validation.property_id.required'),
                'property_id.integer' => __('property_image.validation.property_id.integer'),
                'property_id.exists' => __('property_image.validation.property_id.exists'),
                'image_url.required' => __('property_image.validation.image_url.required'),
                'image_url.string' => __('property_image.validation.image_url.string'),
                'image_url.max' => __('property_image.validation.image_url.max'),
                'id_image_cloudinary.required' => __('property_image.validation.id_image_cloudinary.required'),
                'id_image_cloudinary.string' => __('property_image.validation.id_image_cloudinary.string'),
                'id_image_cloudinary.max' => __('property_image.validation.id_image_cloudinary.max'),
                'image_type.required' => __('property_image.validation.image_type.required'),
                'image_type.integer' => __('property_image.validation.image_type.integer'),
            ]
        );
    }

    /**
     * Validate update property image request
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function updateValidation(Request $request, int $id)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:property_images,id',
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
                'id.required' => __('property_image.validation.id.required'),
                'id.integer' => __('property_image.validation.id.integer'),
                'id.exists' => __('property_image.validation.id.exists'),
                'image_url.required' => __('property_image.validation.image_url.required'),
                'image_url.string' => __('property_image.validation.image_url.string'),
                'image_url.max' => __('property_image.validation.image_url.max'),
                'image_url.url' => __('property_image.validation.image_url.url'),
                'id_image_cloudinary.required' => __('property_image.validation.id_image_cloudinary.required'),
                'id_image_cloudinary.string' => __('property_image.validation.id_image_cloudinary.string'),
                'id_image_cloudinary.max' => __('property_image.validation.id_image_cloudinary.max'),
                'image_type.required' => __('property_image.validation.image_type.required'),
                'image_type.integer' => __('property_image.validation.image_type.integer'),
                'image_type.in' => __('property_image.validation.image_type.in'),
            ]
        );
    }

    /**
     * Validate destroy property image request
     *
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function destroyValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:property_images,id',
                ],
            ],
            [
                'id.required' => __('property_image.validation.id.required'),
                'id.integer' => __('property_image.validation.id.integer'),
                'id.exists' => __('property_image.validation.id.exists'),
            ]
        );
    }

    /**
     * Validate sort property images request
     *
     * @param Request $request
     * @param int $propertyId
     * @return \Illuminate\Validation\Validator
     */
    public function sortValidation(Request $request, int $propertyId): \Illuminate\Validation\Validator
    {
        return Validator::make(array_merge($request->all(), ['property_id' => $propertyId]), [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:property_images,id'],
        ], [
                'property_id.required' => __('property_image.validation.property_id.required'),
                'property_id.integer' => __('property_image.validation.property_id.integer'),
                'property_id.exists' => __('property_image.validation.property_id.exists'),
            'ids.required'   => __('property_image.validation.ids.required'),
            'ids.array'      => __('property_image.validation.ids.array'),
            'ids.*.integer'  => __('property_image.validation.ids.integer'),
            'ids.*.distinct' => __('property_image.validation.ids.distinct'),
            'ids.*.exists'   => __('property_image.validation.ids.exists'),
        ]);
    }
}
