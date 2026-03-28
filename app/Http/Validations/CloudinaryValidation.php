<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class CloudinaryValidation
{
    /**
     * Validate upload single image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function uploadImageValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'image' => [
                    'required',
                    'image',
                    'mimes:jpeg,jpg,png,gif,webp',
                    'max:' . config('const.CLOUDINARY_MAX_IMAGE_SIZE'),
                ],
                'folder' => [
                    'required',
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'image.required' => __('cloudinary.validation.image.required'),
                'image.image' => __('cloudinary.validation.image.image'),
                'image.mimes' => __('cloudinary.validation.image.mimes'),
                'image.max' => __('cloudinary.validation.image.max'),
                'folder.required' => __('cloudinary.validation.folder.required'),
                'folder.nullable' => __('cloudinary.validation.folder.nullable'),
                'folder.string' => __('cloudinary.validation.folder.string'),
                'folder.max' => __('cloudinary.validation.folder.max'),
            ]
        );
    }

    /**
     * Validate upload multiple images request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function uploadMultipleImagesValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'images' => [
                    'required',
                    'array',
                    'min:1',
                    'max:' . config('const.CLOUDINARY_MAX_IMAGES'),
                ],
                'images.*' => [
                    'required',
                    'image',
                    'mimes:jpeg,jpg,png,gif,webp',
                    'max:' . config('const.CLOUDINARY_MAX_IMAGE_SIZE'),
                ],
                'folder' => [
                    'required',
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'images.required' => __('cloudinary.validation.images.required'),
                'images.array' => __('cloudinary.validation.images.array'),
                'images.min' => __('cloudinary.validation.images.min'),
                'images.max' => __('cloudinary.validation.images.max'),
                'images.*.required' => __('cloudinary.validation.images.*.required'),
                'images.*.image' => __('cloudinary.validation.images.*.image'),
                'images.*.mimes' => __('cloudinary.validation.images.*.mimes'),
                'images.*.max' => __('cloudinary.validation.images.*.max'),
                'folder.string' => __('cloudinary.validation.folder.string'),
                'folder.required' => __('cloudinary.validation.folder.required'),
                'folder.nullable' => __('cloudinary.validation.folder.nullable'),
                'folder.max' => __('cloudinary.validation.folder.max'),
            ]
        );
    }

    /**
     * Validate delete image request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function deleteImageValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'public_id' => [
                    'required',
                    'string',
                ],
            ],
            [
                'public_id.required' => __('cloudinary.validation.public_id.required'),
                'public_id.string' => __('cloudinary.validation.public_id.string'),
            ]
        );
    }
}
