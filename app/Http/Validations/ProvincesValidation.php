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
}
