<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\AuthValidation;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $authValidation;
    protected $authService;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        AuthValidation $authValidation,
        AuthService $authService
    ) {
        $this->authValidation = $authValidation;
        $this->authService    = $authService;
    }

    /**
     * Create and return a refresh token for the currently authenticated user.
     * @return JsonResponse
     */
    public function refreshToken(): JsonResponse
    {
        if (! Auth::check()) {
            return $this->errorResponse(
                __('auth.unauthenticated'),
                null,
                HttpStatus::UNAUTHORIZED
            );
        }

        $tokenResult = $this->authService->handleRefreshToken();
        if (! $tokenResult['status']) {
            return $this->errorResponse(
                $tokenResult['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }
        return $this->successResponse(
            ['token' => $tokenResult['token']],
            __('auth.refresh_success'),
            HttpStatus::OK
        );
    }

    /**
     * Send mail reset password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMailResetPassword(Request $request): JsonResponse
    {
        $validated = $this->authValidation->sendMailResetPasswordValidation($request);
        if ($validated->fails()) {
            return $this->validateError(
                $validated->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $sendMailResult = $this->authService->sendMailResetPassword($request);

        if ($sendMailResult['success']) {
            return $this->successResponse(null, $sendMailResult['message']);
        }

        return $this->errorResponse(
            $sendMailResult['message'],
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Summary of register
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = $this->authValidation->validateCreate($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $this->authService->handleRegister($request);

        if ($data) {
            return $this->createdResponse(
                [
                    'name'  => $data['name'],
                    'email' => $data['email'],
                ],
                __('auth.register_success')
            );
        }

        return $this->errorResponse(
            __('auth.register_error'),
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Summary of verifyEmail
     * @param string $token
     * @return JsonResponse
     */
    public function verifyEmail(string $token): JsonResponse
    {
        $validator = $this->authValidation->validateVerifyEmail($token);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors()->first(),
                null,
                HttpStatus::VALIDATION_ERROR,
                Str::before($validator->errors()->first(), ":")
            );
        }
        $result = $this->authService->verifyEmail($token);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message'],
                HttpStatus::OK
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::BAD_REQUEST,
            $result['data']
        );
    }

    /**
     * Summary of handleResetTokenVerify
     * @param Request $request
     * @return JsonResponse
     */
    public function handleResetTokenVerify(Request $request): JsonResponse
    {
        $validator = $this->authValidation->validateVerifyEmail($request->token);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->authService->handleResetTokenVerify($request);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message'],
                HttpStatus::OK
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Summary of adminLogin
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $vali = $this->authValidation->validateAuthLoginRequest($request);
        if ($vali->fails()) {
            return $this->validateError(
                $vali->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        // Only allow ADMIN to login via admin portal
        $result = $this->authService->handleLogin($request, [\App\Enums\UserType::ADMIN]);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::UNAUTHORIZED
        );
    }

    /**
     * Authenticate stay guest portal end-user.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stayLogin(Request $request): JsonResponse
    {
        $vali = $this->authValidation->validateAuthLoginRequest($request);
        if ($vali->fails()) {
            return $this->validateError(
                $vali->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        // Only allow USER (stay guest) to login via stay portal endpoint
        $result = $this->authService->handleLogin($request, [\App\Enums\UserType::USER]);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::UNAUTHORIZED
        );
    }

    /**
     * Summary of partnerLogin
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function partnerLogin(Request $request): JsonResponse
    {
        $vali = $this->authValidation->validateAuthLoginRequest($request);
        if ($vali->fails()) {
            return $this->validateError(
                $vali->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        // Allow PARTNER and ADMIN to login via partner portal
        $result = $this->authService->handleLogin($request, [\App\Enums\UserType::PARTNER, \App\Enums\UserType::ADMIN]);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::UNAUTHORIZED
        );
    }

    /**
     * Summary of logout
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->handleLogout($request);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::BAD_REQUEST,
            $result['data']
        );
    }

    /**
     * Summary of checkPermission
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPermission(): JsonResponse
    {
        $result = $this->authService->checkPermission();

        if ($result['status']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Summary of setPassword
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     */
    public function setPassword(Request $request, $token): JsonResponse
    {
        $validator = $this->authValidation->validateSetPassword($request, $token);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->authService->handleSetPassword($request->input('password'), $token);

        if ($result['status']) {
            return $this->successResponse(
                $result['data'],
                $result['message'],
                HttpStatus::OK
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Submit partner onboarding details and files.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitOnboarding(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return $this->errorResponse(__('auth.unauthorized'), null, HttpStatus::UNAUTHORIZED);
        }

        $result = $this->authService->submitOnboarding($request, $user);

        if ($result['status']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Sign the onboarding E-Contract.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function signContract(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return $this->errorResponse(__('auth.unauthorized'), null, HttpStatus::UNAUTHORIZED);
        }

        $result = $this->authService->signContract($request, $user);

        if ($result['status']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Resubmit partner onboarding details after rejection.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resubmitOnboarding(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return $this->errorResponse(__('auth.unauthorized'), null, HttpStatus::UNAUTHORIZED);
        }

        $result = $this->authService->resubmitOnboarding($request, $user);

        if ($result['status']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }
}
