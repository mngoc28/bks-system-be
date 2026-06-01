<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class TouristSpotValidation
{
    public function indexValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'keyword' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function storeValidation(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tourist_spots,slug'],
            'category' => ['required', 'string', 'max:50'],
            'region_label' => ['nullable', 'string', 'max:255'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    public function updateValidation(Request $request, int $id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tourist_spots', 'slug')->ignore($id)
            ],
            'category' => ['sometimes', 'required', 'string', 'max:50'],
            'region_label' => ['nullable', 'string', 'max:255'],
            'province_id' => ['sometimes', 'required', 'integer', 'exists:provinces,id'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
