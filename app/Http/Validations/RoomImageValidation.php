<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class RoomImageValidation
{
    /**
     * Validate get images by room ID request
     *
     * @param int $roomId
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function getByRoomIdValidation(int $roomId)
    {
        return Validator::make(
            ['room_id' => $roomId],
            [
                'room_id' => [
                    'required',
                    'integer',
                    'exists:rooms,id',
                ],
            ],
            [
                'room_id.required' => __('room_image.validation.room_id.required'),
                'room_id.integer' => __('room_image.validation.room_id.integer'),
                'room_id.exists' => __('room_image.validation.room_id.exists'),
            ]
        );
    }

    /**
     * Validate show room image request
     *
     * @param int $id
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
                    'exists:room_images,id',
                ],
            ],
            [
                'id.required' => __('room_image.validation.id.required'),
                'id.integer' => __('room_image.validation.id.integer'),
                'id.exists' => __('room_image.validation.id.exists'),
            ]
        );
    }

    /**
     * Validate store room image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'room_id' => [
                    'required',
                    'integer',
                    'exists:rooms,id',
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
                    'in:' . implode(',', \App\Enums\ImageType::values()),
                ],
            ],
            [
                'room_id.required' => __('room_image.validation.room_id.required'),
                'room_id.integer' => __('room_image.validation.room_id.integer'),
                'room_id.exists' => __('room_image.validation.room_id.exists'),
                'image_url.required' => __('room_image.validation.image_url.required'),
                'image_url.string' => __('room_image.validation.image_url.string'),
                'image_url.max' => __('room_image.validation.image_url.max'),
                'id_image_cloudinary.required' => __('room_image.validation.id_image_cloudinary.required'),
                'id_image_cloudinary.string' => __('room_image.validation.id_image_cloudinary.string'),
                'id_image_cloudinary.max' => __('room_image.validation.id_image_cloudinary.max'),
                'image_type.required' => __('room_image.validation.image_type.required'),
                'image_type.integer' => __('room_image.validation.image_type.integer'),
                'image_type.in' => __('room_image.validation.image_type.in'),
            ]
        );
    }

    /**
     * Validate update room image type request
     *
     * @param Request $request
     * @param array $updates
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function updateTypeValidation(Request $request, array $updates)
    {
        return Validator::make(
            $request->all(),
            [
                'updates' => [
                    'required',
                    'array',
                    'min:1',
                ],
                'updates.*.id' => [
                    'required',
                    'integer',
                    'exists:room_images,id',
                ],
                'updates.*.image_type' => [
                    'required',
                    'integer',
                    'in:' . implode(',', \App\Enums\ImageType::values()),
                ],
            ],
            [
                'updates.required' => __('room_image.validation.updates.required'),
                'updates.array' => __('room_image.validation.updates.array'),
                'updates.min' => __('room_image.validation.updates.min'),
                'updates.*.id.required' => __('room_image.validation.id.required'),
                'updates.*.id.integer' => __('room_image.validation.id.integer'),
                'updates.*.id.exists' => __('room_image.validation.id.exists'),
                'updates.*.image_type.required' => __('room_image.validation.image_type.required'),
                'updates.*.image_type.integer' => __('room_image.validation.image_type.integer'),
                'updates.*.image_type.in' => __('room_image.validation.image_type.in'),
            ]
        );
    }

    /**
     * Summary of updateSortValidation
     * @param int $imageId
     * @param int $imageIdA
     * @param int $imageIdB
     * @return \Illuminate\Validation\Validator
     */
    public function updateSortValidation(int $roomId, int $imageIdA, int $imageIdB)
    {
        return Validator::make(
            [
                'room_id' => $roomId,
                'image_id_a' => $imageIdA,
                'image_id_b' => $imageIdB,
            ],
            [
                'room_id' => [
                    'required',
                    'integer',
                    'exists:rooms,id',
                ],
                'image_id_a' => [
                    'required',
                    'integer',
                    'exists:room_images,id',
                ],
                'image_id_b' => [
                    'required',
                    'integer',
                    'exists:room_images,id',
                ],
            ],
            [
                'room_id.required' => __('room_image.validation.room_id.required'),
                'room_id.integer' => __('room_image.validation.room_id.integer'),
                'room_id.exists' => __('room_image.validation.room_id.exists'),
                'image_id_a.required' => __('room_image.validation.image_id_a.required'),
                'image_id_a.integer' => __('room_image.validation.image_id_a.integer'),
                'image_id_a.exists' => __('room_image.validation.image_id_a.exists'),
                'image_id_b.required' => __('room_image.validation.image_id_b.required'),
                'image_id_b.integer' => __('room_image.validation.image_id_b.integer'),
                'image_id_b.exists' => __('room_image.validation.image_id_b.exists'),
            ]
        );
    }

    /**
     * Validate destroy room image request
     *
     * @param array $ids
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function destroyValidation(array $ids)
    {
        return Validator::make(
            ['ids' => $ids],
            [
                'ids' => [
                    'required',
                    'array',
                    'min:1',
                ],
                'ids.*' => [
                    'required',
                    'integer',
                    'exists:room_images,id',
                ],
            ],
            [
                'ids.required' => __('room_image.validation.ids.required'),
                'ids.array' => __('room_image.validation.ids.array'),
                'ids.min' => __('room_image.validation.ids.min'),
                'ids.*.required' => __('room_image.validation.id.required'),
                'ids.*.integer' => __('room_image.validation.id.integer'),
                'ids.*.exists' => __('room_image.validation.id.exists'),
            ]
        );
    }
}
