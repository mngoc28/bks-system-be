<?php

namespace App\Http\Validations;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Validator;

class WardValidation
{
    /**
     * Validate get wards by province id request
     * @param int $provinceId
     * @return \Illuminate\Validation\Validator
     */
    public function getWardsByProvinceIdValidation(int $provinceId): ?\Illuminate\Validation\Validator
    {
        return Validator::make(['province_id' => $provinceId], [
            'province_id' => 'required|integer|exists:provinces,id',
        ], [
            'province_id.required' => __('ward.validation.province_id.required'),
            'province_id.integer' => __('ward.validation.province_id.integer'),
            'province_id.exists' => __('ward.validation.province_id.exists'),
        ]);
    }
}
