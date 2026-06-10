<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserValidation
{
    /**
     * Summary of validateGetUser
     * @param array $data
     * @return \Illuminate\Validation\Validator|null
     */
    public function validateGetUser(array $data): ?\Illuminate\Validation\Validator
    {
        if (! isset($data['id']) || $data['id'] === null) {
            return null;
        }

        return Validator::make(
            $data,
            [
                'id' => ['required', 'integer', 'exists:users,id'],
            ],
            [
                'id.required' => __('user.id_required'),
                'id.integer'  => __('user.id_integer'),
                'id.exists'   => __('user.id_exists'),
            ]
        );
    }

    /**
     * Summary of validateUpdate
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateUpdate(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'id'    => 'sometimes|integer|exists:users,id',
            'name'  => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'role'  => 'sometimes|string|in:admin,partner,user',
            'avatar' => 'sometimes|nullable|string|max:255',
            'id_avatar' => 'sometimes|nullable|string|max:255',
        ], [
            'name.max'     => __('user.name_max'),
            'email.email'  => __('user.email_invalid'),
            'email.max'    => __('user.email_max'),
            'phone.max'    => __('user.phone_max'),
            'role.string'  => __('user.role_string'),
            'role.in'      => __('user.role_in'),
        ]);
    }

    /**
     * Summary of validateChangePassword
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateChangePassword(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'current_password'          => 'required|string',
            'new_password'              => [
                'required',
                'string',
                'min:8',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*]/',
                'different:current_password'
            ],
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'current_password.required'          => __('user.current_password_required'),
            'new_password.required'              => __('user.new_password_required'),
            'new_password.min'                   => __('user.new_password_min'),
            'new_password.regex'                 => __('user.new_password_regex'),
            'new_password.different'             => __('user.new_password_different'),
            'new_password_confirmation.required' => __('user.new_password_confirmation_required'),
            'new_password_confirmation.same'     => __('user.new_password_confirmation_same'),
        ]);
    }

    /**
     * Validate reset password by admin (no current password required)
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateResetPassword(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'new_password'              => 'required|string|min:8|regex:/[a-zA-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'new_password.required'              => __('user.new_password_required'),
            'new_password.min'                   => __('user.new_password_min'),
            'new_password.regex'                 => __('user.new_password_regex'),
            'new_password_confirmation.required' => __('user.new_password_confirmation_required'),
            'new_password_confirmation.same'     => __('user.new_password_confirmation_same'),
        ]);
    }

    /**
     * Summary of validateCreate
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateCreate(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:255|unique:users,email',
            'phone'                 => 'sometimes|nullable|string|max:20',
            'password'              => 'required|string|min:8|regex:/[a-zA-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
            'password_confirmation' => 'required|string|same:password',
            'role'                  => 'nullable|in:admin,partner,user',
            'status'                => 'nullable|in:0,1,2',
            'type'                  => 'sometimes|integer',
        ], [
            'name.required'                  => __('user.name_required'),
            'name.max'                       => __('user.name_max'),
            'email.required'                 => __('user.email_required'),
            'email.email'                    => __('user.email_invalid'),
            'email.max'                      => __('user.email_max'),
            'email.unique'                   => __('user.email_unique'),
            'phone.max'                      => __('user.phone_max'),
            'password.required'              => __('user.password_required'),
            'password.min'                   => __('user.password_min'),
            'password.regex'                 => __('user.password_regex'),
            'password_confirmation.required' => __('user.password_confirmation_required'),
            'password_confirmation.same'     => __('user.password_confirmation_same'),
            'role.in'                        => __('user.role_in'),
            'status.in'                      => __('user.status_in'),
        ]);
    }
    /**
     * Summary of validateDelete
     * @param $id
     * @return \Illuminate\Validation\Validator
     */
    public function validateDelete($id): \Illuminate\Validation\Validator
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:users,id',
        ], [
            'id.required' => __('user.id_required'),
            'id.integer'  => __('user.id_integer'),
            'id.exists'   => __('user.id_exists'),
        ]);
    }

    /**
     * Validate admin status update (activate / block / unblock).
     */
    public function validateUpdateStatus(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'status' => 'required|integer|in:1,2',
        ], [
            'status.required' => __('user.status_required'),
            'status.in' => __('user.status_in'),
        ]);
    }

    /**
     * Validate filters for getting all users
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateGetAll(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'q'          => 'nullable|string|max:255',
            'email'      => 'nullable|string|max:255',
            'role'       => 'nullable|string|in:admin,partner,user',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'nullable|in:0,1,2',
            'created_at' => 'nullable|date|date_format:Y-m-d',
            'page'       => 'nullable|integer|min:1',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ], [
            'q.string'               => __('user.q_string'),
            'q.max'                  => __('user.q_max'),
            'email.string'           => __('user.email_string'),
            'email.max'              => __('user.email_max'),
            'role.string'            => __('user.role_string'),
            'role.in'                => __('user.role_in'),
            'phone.string'           => __('user.phone_string'),
            'phone.max'              => __('user.phone_max'),
            'status.in'              => __('user.status_in_filter'),
            'created_at.date'        => __('user.created_at_date'),
            'created_at.date_format' => __('user.created_at_date_format'),
            'page.integer'           => __('user.page_integer'),
            'page.min'               => __('user.page_min'),
            'per_page.integer'       => __('user.per_page_integer'),
            'per_page.min'           => __('user.per_page_min'),
            'per_page.max'           => __('user.per_page_max'),
        ]);
    }

    /**
     * Summary of storeValidation
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateUploadAvatar(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'image' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:' . config('const.CLOUDINARY_MAX_IMAGE_SIZE'),
                ],
                'folder' => [
                    'sometimes',
                    'string',
                    'max:255',
                ],
            ],
            [
                'user_id.required' => __('user.validation.user_id.required'),
                'user_id.integer' => __('user.validation.user_id.integer'),
                'user_id.exists' => __('user.validation.user_id.exists'),
                'image.required' => __('user.validation.image.required'),
                'image.image' => __('user.validation.image.image'),
                'image.mimes' => __('user.validation.image.mimes'),
                'image.max' => __('user.validation.image.max'),
                'folder.string' => __('user.validation.folder.string'),
                'folder.max' => __('user.validation.folder.max'),
            ]
        );
    }
}
