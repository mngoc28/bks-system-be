<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyTypeValidation
{
    /**
     * Validate property types listing request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function indexValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'pagination' => ['nullable', 'integer', 'min:1'],
            ],
            [
                'pagination.integer' => __('property_type.pagination_integer'),
                'pagination.min' => __('property_type.pagination_min'),
            ]
        );
    }

    /**
     * Validate property type creation request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function storeValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:property_types,slug'],
                'description' => ['nullable', 'string'],
                'icon_url' => ['nullable', 'string', 'max:255'],
                'is_active' => ['required', 'boolean'],
            ],
            [
                'name.required' => __('property_type.name_required'),
                'name.string' => __('property_type.name_string'),
                'name.max' => __('property_type.name_max'),
                'slug.string' => __('property_type.slug_string'),
                'slug.max' => __('property_type.slug_max'),
                'slug.unique' => __('property_type.slug_unique'),
                'description.string' => __('property_type.description_string'),
                'icon_url.string' => __('property_type.icon_url_string'),
                'icon_url.max' => __('property_type.icon_url_max'),
                'is_active.required' => __('property_type.is_active_required'),
                'is_active.boolean' => __('property_type.is_active_boolean'),
            ]
        );
    }

    /**
     * Validate property type detail request.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function detailValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => ['required', 'integer', 'exists:property_types,id'],
            ],
            [
                'id.required' => __('property_type.id_required'),
                'id.integer' => __('property_type.id_integer'),
                'id.exists' => __('property_type.id_not_found'),
            ]
        );
    }

    /**
     * Validate property type update request.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateValidation(int $id, Request $request)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => ['required', 'integer', 'exists:property_types,id'],
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:property_types,slug,' . $id],
                'description' => ['nullable', 'string'],
                'icon_url' => ['nullable', 'string', 'max:255'],
                'is_active' => ['required', 'boolean'],
            ],
            [
                'id.required' => __('property_type.id_required'),
                'id.integer' => __('property_type.id_integer'),
                'id.exists' => __('property_type.id_not_found'),
                'name.required' => __('property_type.name_required'),
                'name.string' => __('property_type.name_string'),
                'name.max' => __('property_type.name_max'),
                'slug.string' => __('property_type.slug_string'),
                'slug.max' => __('property_type.slug_max'),
                'slug.unique' => __('property_type.slug_unique'),
                'description.string' => __('property_type.description_string'),
                'icon_url.string' => __('property_type.icon_url_string'),
                'icon_url.max' => __('property_type.icon_url_max'),
                'is_active.required' => __('property_type.is_active_required'),
                'is_active.boolean' => __('property_type.is_active_boolean'),
            ]
        );
    }

    /**
     * Validate property type status update request.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateStatusValidation(int $id, Request $request)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => ['required', 'integer', 'exists:property_types,id'],
                'is_active' => ['required', 'boolean'],
            ],
            [
                'id.required' => __('property_type.id_required'),
                'id.integer' => __('property_type.id_integer'),
                'id.exists' => __('property_type.id_not_found'),
                'is_active.required' => __('property_type.is_active_required'),
                'is_active.boolean' => __('property_type.is_active_boolean'),
            ]
        );
    }
}
