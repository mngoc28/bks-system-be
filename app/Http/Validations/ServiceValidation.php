<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ServiceValidation
{
    /**
     * Validate search service request parameters.
     *
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public static function searchServiceValidation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'query' => ['nullable', 'string', 'max:100'],
                'price_min' => ['nullable', 'numeric', 'min:0'],
                'price_max' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'query.string' => __('service.validation.query_string'),
                'query.max' => __('service.validation.query_max'),
                'price_min.numeric' => __('service.validation.price_min_numeric'),
                'price_min.min' => __('service.validation.price_min_min'),
                'price_max.numeric' => __('service.validation.price_max_numeric'),
                'price_max.min' => __('service.validation.price_max_min'),
            ]
        );
    }


    /**
     * Validate show service request parameters.
     *
     * @param int $id The ID of the service to show.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public static function show($id): \Illuminate\Contracts\Validation\Validator
    {

        return Validator::make(
            ['id' => $id],
            [
            'id' => ['required', 'integer', 'exists:services,id']
            ],
            [
            'id.required' => __('service.validation.id_required'),
            'id.integer' => __('service.validation.id_integer'),
            'id.exists' => __('service.validation.id_exists'),
            ]
        );
    }

    /**
     * Validate create service request parameters.
     *
     * @param Request $request The incoming HTTP request.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */

    public static function store(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:100', 'unique:services,name'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string']
            ],
            [
            'name.required' => __('service.validation.name_required'),
            'name.string' => __('service.validation.name_string'),
            'name.max' => __('service.validation.name_max'),
            'name.unique' => __('service.validation.name_unique'),
            'price.required' => __('service.validation.price_required'),
            'price.numeric' => __('service.validation.price_numeric'),
            'price.min' => __('service.validation.price_min'),
            'description.string' => __('service.validation.description_string')
            ]
        );
    }

    /**
     * Validate update service request parameters.
     *
     * @param int $id The ID of the service to update.
     * @param array $data The incoming request data.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    public static function update($request, $id): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => ['required', 'integer', 'exists:services,id'],
                'name' => ['required', 'string', 'max:100', 'unique:services,name,'.$id],
                'price' => ['required', 'numeric', 'min:0'],
                'description' => ['nullable', 'string']
            ],
            [
                'id.required' => __('service.validation.id_required'),
                'id.integer' => __('service.validation.id_integer'),
                'id.exists' => __('service.validation.id_exists'),
                'name.required' => __('service.validation.name_required'),
                'name.string' => __('service.validation.name_string'),
                'name.max' => __('service.validation.name_max'),
                'name.unique' => __('service.validation.name_unique'),
                'price.required' => __('service.validation.price_required'),
                'price.numeric' => __('service.validation.price_numeric'),
                'price.min' => __('service.validation.price_min'),
                'description.string' => __('service.validation.description_string')
            ]
        );
    }

    /**
     * Validate delete service request parameters.
     *
     * @param int $id The ID of the service to delete.
     * @return int The validated service ID.
     */
    public static function delete($id)
    {
        $validator = Validator::make(
            ['id' => $id],
            [
            'id' => ['required', 'integer', 'exists:services,id']
            ],
            [
            'id.required' => __('service.validation.id_required'),
            'id.integer' => __('service.validation.id_integer'),
            'id.exists' => __('service.validation.id_exists')
            ]
        );
    }
}
