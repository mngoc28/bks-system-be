<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProvincesValidation
{
    /**
     * Validation data for searching provinces
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function searchProvinceValidation(
        Request $request
    ): \Illuminate\Validation\Validator {
        return Validator::make($request->all(), []);
    }

    /**
     * Validation data for updating a province
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Validation\Validator
     */
    public function updateProvinceValidation(
        Request $request,
        int $id
    ): \Illuminate\Validation\Validator {
        return Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100|unique:provinces,name,' . $id,
            'name_en' => 'sometimes|required|string|max:100',
            'image' => 'nullable|string|max:255',
        ]);
    }
}
