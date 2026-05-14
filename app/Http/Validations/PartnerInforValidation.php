<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class PartnerInforValidation
{
    /**
     * Validation Search partner information request
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function searchPartnerInforValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_name' => ['nullable', 'string', 'max:255'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'ward_name' => ['nullable', 'string', 'max:100'],
                'province_name' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', 'in:0,1,2'],
                'page'          => ['nullable', 'integer', 'min:1'],
                'per_page'      => ['nullable', 'integer', 'min:1'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'website' => ['nullable', 'string', 'max:255'],
            ],
            [
                'user_name.max' => __('partner.validation.name.max'),
                'company_name.max' => __('partner.validation.company_name.max'),
                'ward_name.max' => __('partner.validation.ward_name.max'),
                'province_name.max' => __('partner.validation.province_name.max'),
                'status.in' => __('partner.validation.status.in'),
                'phone.max' => __('partner.validation.phone.max'),
                'address.max' => __('partner.validation.address.max'),
                'website.max' => __('partner.validation.website.max'),
                'page.integer'      => __('pagination.page.integer'),
                'page.min'          => __('pagination.page.min'),
                'per_page.integer'  => __('pagination.per_page.integer'),
                'per_page.min'      => __('pagination.per_page.min'),
            ],
            [
                'user_name' => __('partner.attributes.name'),
                'company_name' => __('partner.attributes.company_name'),
                'ward_name' => __('partner.attributes.ward_name'),
                'province_name' => __('partner.attributes.province_name'),
                'status' => __('partner.attributes.status'),
                'phone' => __('partner.attributes.phone'),
                'address' => __('partner.attributes.address'),
                'website' => __('partner.attributes.website'),
            ]
        );
    }

    /**
     * Validate detail partner information request
     *
     * @param int $partner
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function detailPartnerInforValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:partner_info,id',
                ]
            ],
            [
                'id.required' => __('partner.validation.id.required'),
                'id.integer' => __('partner.validation.id.integer'),
                'id.exist' => __('partner.validation.id.exists'),
            ],
            [
                'id' => __('property.attributes.id'),
            ]
        );
    }

    /**
     * Validate update partner information request
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function updatePartnerInforValidation(Request $request, int $id)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => [
                    'required',
                    'integer',
                    'exists:partner_info,id',
                ],
                'company_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9+\-\(\)\s]+$/',
                ],
                'website' => [
                    'nullable',
                    'string',
                    'max:255',
                    'url',
                ],
                'description' => 'nullable|string|max:255',
                'image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'image_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ],
            [
                'id.required' => __('partner.validation.id.required'),
                'id.integer' => __('partner.validation.id.integer'),
                'id.exists' => __('partner.validation.id.exists'),
                'company_name.max' => __('partner.validation.company_name.max'),
                'address.max' => __('partner.validation.address.max'),
                'phone.max' => __('partner.validation.phone.max'),
                'phone.regex' => __('partner.validation.phone.regex'),
                'website.url' => __('partner.validation.website.url'),
                'website.max' => __('partner.validation.website.max'),
                'description.max' => __('partner.validation.description.max'),
                'image_1.image' => __('partner.validation.image_1.image'),
                'image_1.mimes' => __('partner.validation.image_1.mimes'),
                'image_1.max' => __('partner.validation.image_1.max'),
                'image_2.image' => __('partner.validation.image_2.image'),
                'image_2.mimes' => __('partner.validation.image_2.mimes'),
                'image_2.max' => __('partner.validation.image_2.max'),
                'image_3.image' => __('partner.validation.image_3.image'),
                'image_3.mimes' => __('partner.validation.image_3.mimes'),
                'image_3.max' => __('partner.validation.image_3.max'),
            ],
            [
                'id' => __('partner.attributes.id'),
                'company_name' => __('partner.attributes.company_name'),
                'phone' => __('partner.attributes.phone'),
                'address' => __('partner.attributes.address'),
                'website' => __('partner.attributes.website'),
                'description' => __('partner.attributes.description'),
                'image_1' => __('partner.attributes.image_1'),
                'image_2' => __('partner.attributes.image_2'),
                'image_3' => __('partner.attributes.image_3'),
            ]
        );
    }
}
