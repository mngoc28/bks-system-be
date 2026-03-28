<?php

namespace App\Http\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

final class AmenityValidation
{
    /**
     * Validate get amenity by ID request
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getByIdValidation($id)
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:amenities,id',
        ], [
            'id.required' => __('amenity.id_required'),
            'id.integer'  => __('amenity.id_integer'),
            'id.exists'   => __('amenity.id_exists'),
        ]);
    }

    /**
     * Validate index request for amenities
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function indexValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'nullable|string|max:50',
            'sort_field' => 'nullable|in:id,name,created_at,updated_at',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ], [
            'sort_field.in' => __('amenity.sort_field_invalid'),
            'sort_direction.in' => __('amenity.sort_direction_invalid'),
            'per_page.integer' => __('amenity.per_page_integer'),
            'per_page.min' => __('amenity.per_page_min'),
            'per_page.max' => __('amenity.per_page_max'),
            'page.integer' => __('amenity.page_integer'),
            'page.min' => __('amenity.page_min'),
        ]);
    }

    /**
     * Validate create amenity request
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function createValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:amenities,name',
        ], [
            'name.required' => __('amenity.name_required'),
            'name.string' => __('amenity.name_string'),
            'name.max' => __('amenity.name_max'),
            'name.unique' => __('amenity.name_unique'),
        ]);
    }

    /**
     * Validate update amenity request
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateValidation($id, Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:amenities,name,' . $id,
        ], [
            'name.required' => __('amenity.name_required'),
            'name.string' => __('amenity.name_string'),
            'name.max' => __('amenity.name_max'),
            'name.unique' => __('amenity.name_unique'),
        ]);
    }
}
