<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use App\Enums\UserType;
use App\Rules\EmailExistsByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthValidation
{
    /**
     * Validate send mail reset password
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     * @throws BusinessException
     */
    public function sendMailResetPasswordValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                "user_type" => [
                    "required",
                    "string",
                    "max:50",
                    "in:" . implode(",", UserType::getValues()),
                ],
                "email" => [
                    "required",
                    "max:255",
                    new EmailExistsByRole($request->input('user_type')),
                ],
            ],
            [
                "user_type.required" => __('auth.user_type_required'),
                "user_type.string" => __('auth.user_type_string'),
                "user_type.in" => __('auth.user_type_in'),
                "user_type.max" => __('auth.user_type_max'),
                "email.required" => __('auth.email_required'),
                "email.max" => __('auth.email_max'),
            ]
        );
    }

    /**
     * Validate login
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateAuthLoginRequest(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8|regex:/[a-zA-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/'
            ],
            [
                'email.required' => __('auth.email_required'),
                'email.email' => __('auth.email_email'),
                'email.max' => __('auth.email_max'),
                'password.required' => __('auth.password_required'),
                'password.string' => __('auth.password_string'),
                'password.min' => __('auth.password_min'),
                'password.regex' => __('auth.password_regex'),
                'user_type.required' => __('auth.user_type_required'),
                'user_type.in' => __('auth.user_type_in'),
            ]
        );
    }

    /**
     * Validate create user
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateCreate(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|string|min:8|regex:/[a-zA-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
                'phone' => 'nullable|string|max:20',
                'role' => 'nullable|in:admin,partner,user',
                'status' => 'nullable|in:0,1,2',
            ],
            [
                'name.required' => __('auth.name_required'),
                'name.string' => __('auth.name_string'),
                'name.max' => __('auth.name_max'),
                'email.required' => __('auth.email_required'),
                'email.email' => __('auth.email_email'),
                'email.unique' => __('auth.email_unique'),
                'email.max' => __('auth.email_max'),
                'password.required' => __('auth.password_required'),
                'password.string' => __('auth.password_string'),
                'password.min' => __('auth.password_min'),
                'password.regex' => __('auth.password_regex'),
                'phone.string' => __('auth.phone_string'),
                'phone.max' => __('auth.phone_max'),
                'role.in' => __('auth.role_in'),
                'status.in' => __('auth.status_in'),
            ]
        );
    }

    /**
     * Validate verify email
     *
     * @param string $token
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    public function validateVerifyEmail(string $token): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            ['token' => $token],
            [
                'token' => ['required', 'string', 'exists:users,verification_token'],
            ],
            [
                'token.required' => "VET1: " . __('auth.verify_email_invalid_token'),
                'token.string' => "VET2: " . __('auth.verify_email_invalid_token'),
                'token.exists' => "VET4: " . __('auth.verify_email_token_not_found'),
            ]
        );
    }

    public function validateSetPassword(Request $request, string $token): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
            array_merge($request->all(), ['token' => $token]),
            [
                'token' => ['required', 'string', 'exists:users,verification_token'],
                'password' => 'required|string|min:8|regex:/[a-zA-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
                'password_confirmation' => 'required|string|same:password',
            ],
            [
                'token.required' => __('auth.reset_password_invalid_token'),
                'token.string' => __('auth.reset_password_invalid_token'),
                'token.exists' => __('auth.reset_password_invalid_token'),
                'password.required' => __('auth.password_required'),
                'password.string' => __('auth.password_string'),
                'password.min' => __('auth.password_min'),
                'password.regex' => __('auth.password_regex'),
                'password_confirmation.required' => __('auth.password_confirmation_required'),
                'password_confirmation.string' => __('auth.password_confirmation_string'),
                'password_confirmation.same' => __('auth.password_confirmation_not_match'),
            ]
        );
    }
}
