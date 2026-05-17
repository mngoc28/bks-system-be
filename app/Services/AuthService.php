<?php

namespace App\Services;

use App\Enums\Status as EnumsStatus;
use App\Enums\UserType;
use App\Jobs\VerifyMail;
use App\Repositories\PartnerInforRepository\PartnerInforRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $adminRepository;
    protected $usersRepository;
    protected $partnerInfoRepository;

    /**
     * Constructor
     * @param UsersRepositoryInterface $usersRepository
     */
    public function __construct(
        UsersRepositoryInterface $usersRepository,
        PartnerInforRepositoryInterface $partnerInfoRepository
    ) {
        $this->usersRepository = $usersRepository;
        $this->partnerInfoRepository = $partnerInfoRepository;
    }

    /**
     * Create (or update) a JWT refresh token for a given User instance.
     * Extracted so callers that already have the User can pass it directly.
     *
     * @return array
     */
    public function handleRefreshToken(): array
    {
        try {
            $tokenResult = JWTAuth::refresh(JWTAuth::getToken());

            return [
                'status' => true,
                'token'  => $tokenResult,
            ];
        } catch (\Exception $e) {
            Log::error('Refresh Token Service error: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => __('auth.refresh_error'),
            ];
        }
    }

    /**
     * Send mail reset password
     * @param Request $request
     * @return array
     */
    // public function sendMailResetPassword($request): array
    // {
    //     try {
    //         $this->adminRepository->findOneBy([
    //             'email' => $request->email,
    //         ]);
    //         return [
    //             'success' => true,
    //             'message' => __('auth.send_mail_reset_password_success'),
    //             'status'  => HttpStatus::OK->value,
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => __('auth.send_mail_reset_password_error'),
    //             'status'  => HttpStatus::INTERNAL_SERVER_ERROR->value,
    //         ];
    //     }
    // }

    /**
     * Summary of handleRegister
     * @param mixed $request
     * @return object | null
     */
    public function handleRegister($request): object | null
    {
        try {
            DB::beginTransaction();

            $token = Str::random(20) . time();

            $createPartner = $this->usersRepository->create([
                'name'               => $request->input('name'),
                'email'              => $request->input('email'),
                'password'           => Hash::make($request->input('password')),
                'role'               => UserType::PARTNER,
                'phone'              => $request->input('phone'),
                'avatar'             => $request->input('avatar', null),
                'status'             => EnumsStatus::ACTIVE->value,
                'verification_token' => $token,
                'is_email_verified'  => false,
                'token_expires_at'   => Carbon::now()->addMinutes(config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL')),
            ]);

            $this->usersRepository->update($createPartner->id, [
                'created_by' => $createPartner->id,
                'updated_by' => $createPartner->id,
            ]);

            $this->partnerInfoRepository->create([
                'user_id' => $createPartner->id,
                'company_name' => $request->company_name,
                "province_id" => $request->province_id,
                'ward_id' => $request->ward_id,
                'created_by' => $createPartner->id,
                'updated_by' => $createPartner->id,
            ]);
            // Send mail
            VerifyMail::dispatch($token, $createPartner->name, $createPartner->email);

            DB::commit();

            return $createPartner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Summary of verifyEmail
     * @param string $token
     * @return array
     */
    public function verifyEmail(string $token): array
    {
        try {
            $user = $this->usersRepository->findOneBy([
                'verification_token' => $token,
            ], false);

            if (!$user) {
                return [
                    'status'  => false,
                    'message' => "VET4: " . __('auth.verify_email_invalid_token'),
                    'data'    => "VET4",
                ];
            }

            if ($user->is_email_verified) {
                return [
                    'status'  => false,
                    'message' => "VET5: " . __('auth.verify_email_already_verified'),
                    'data'    => "VET5",
                ];
            }

            if (Carbon::parse($user->token_expires_at)->isPast()) {
                return [
                    'status'  => false,
                    'message' => "VET3: " . __('auth.verify_email_expired_token'),
                    'data'    => "VET3",
                ];
            }

            $this->usersRepository->update($user->id, [
                'is_email_verified'  => true,
                'email_verified_at'  => now(),
                'token_expires_at'   => null,
                'status'             => EnumsStatus::ACTIVE->value,
            ]);

            return [
                'status'  => true,
                'message' => "VET6: " . __('auth.verify_email_success'),
                'data'    => "VET6",
            ];
        } catch (\Exception $e) {
            Log::error('Verify email failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => "VET4: " . __('auth.error_email'),
                'data'    => "VET4",
            ];
        }
    }

    /**
     * Summary of handleResetTokenVerify
     * @param Request $request
     * @return array
     */
    public function handleResetTokenVerify($request): array
    {
        try {
            $user = $this->usersRepository->findOneBy([
                'verification_token' => $request->input('token'),
            ], false);

            if (! $user) {
                return [
                    'status'  => false,
                    'message' => __('auth.verify_email_invalid_token'),
                    'data'    => null,
                ];
            }

            $newToken = Str::random(20) . time();
            $this->usersRepository->update($user->id, [
                'verification_token' => $newToken,
                'token_expires_at'   => Carbon::now()->addMinutes(config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL')),
            ]);

            VerifyMail::dispatch($newToken, $user->name, $user->email);

            return [
                'status'  => true,
                'message' => __('auth.notification_email'),
                'data'    => null,
            ];
        } catch (\Exception $e) {
            Log::error('Reset password token verify failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => __('auth.error_email'),
                'data'    => null,
            ];
        }
    }

    /**
     * Summary of handleLogin
     * @param mixed $request
     * @param array|null $allowedRoles
     * @return array
     */
    public function handleLogin($request, $allowedRoles = null): array
    {
        try {
            $credentials = $request->only(['email', 'password']);

            // Check if user exists first to provide better error message
            $user = $this->usersRepository->findOneBy(['email' => $credentials['email']], false);

            if (!$user) {
                return [
                    'status'  => false,
                    'message' => __('auth.email_not_exists'),
                    'data'    => null,
                ];
            }

            if (! $token = Auth::guard('api')->attempt($credentials)) {
                return [
                    'status'  => false,
                    'message' => __('auth.password'),
                    'data'    => null,
                ];
            }

            // Role validation for specific portal
            if ($allowedRoles && !in_array($user->role, $allowedRoles)) {
                return [
                    'status'  => false,
                    'message' => __('auth.not_permission'),
                    'data'    => null,
                ];
            }

            // Combined role check for portals
            if (!in_array($user->role, [UserType::ADMIN, UserType::PARTNER, UserType::USER])) {
                Auth::guard('api')->logout();
                return [
                    'status'  => false,
                    'message' => __('auth.not_permission'),
                    'data'    => null,
                ];
            }

            if ($user->is_email_verified === false && $user->role !== 'admin') {
                Auth::guard('api')->logout();
                return [
                    'status'  => false,
                    'message' => __('auth.account_not_verified'),
                    'data'    => null,
                ];
            }

            if ((int) $user->status == EnumsStatus::BLOCKED->value) {
                Auth::guard('api')->logout();
                return [
                    'status'  => false,
                    'message' => __('auth.account_blocked'),
                    'data'    => null,
                ];
            }

            if ((int) $user->status == EnumsStatus::PENDING->value) {
                Auth::guard('api')->logout();
                return [
                    'status'  => false,
                    'message' => __('auth.account_not_verified'),
                    'data'    => null,
                ];
            }

            return [
                'status'  => true,
                'message' => __('auth.login_success'),
                'data'    => [
                    'token' => $token,
                    'name' => $user->name,
                    'role' => $user->role,
                    'email' => $user->email,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Login Service error: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => __('auth.login_failed'),
                'data'    => null,
            ];
        }
    }

    /**
     * Handle user logout
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleLogout($request): array
    {
        try {
            if (Auth::guard('api')->check()) {
                Auth::guard('api')->logout();
            }
            return [
                'status'  => true,
                'message' => __('auth.logout_success'),
                'data'    => null,
            ];
        } catch (\Exception $e) {
            Log::error('Logout Service error: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => __('auth.logout_error'),
                'data'    => null,
            ];
        }
    }

    /**
     * Summary of checkPermission
     * @return array
     */
    public function checkPermission(): array
    {
        try {
            $user = Auth::guard('api')->user();
            if (! $user) {
                return [
                    'status'  => false,
                    'data'    => null,
                    'message' => __('auth.unauthorized'),
                ];
            }
            return [
                'status'  => true,
                'data'    => [
                    'role' => $user->role,
                    'name' => $user->name,
                ],
                'message' => __('auth.check_permission_success'),
            ];
        } catch (Exception $e) {
            Log::error('Check permission failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'data'    => null,
                'message' => __('auth.check_permission_failed'),
            ];
        }
    }

    /**
     * Summary of setPassword
     * @param mixed $request
     * @return array
     */
    public function handleSetPassword(string $password, string $token): array
    {
        try {
            $user = $this->usersRepository->findOneBy([
                'verification_token' => $token,
            ]);

            if ($user['is_email_verified']) {
                return [
                    'status'  => false,
                    'message' => "VET5: " . __('auth.verify_email_already_verified'),
                    'data'    => "VET5",
                ];
            }

            if (Carbon::parse($user['token_expires_at'])->isPast()) {
                return [
                    'status'  => false,
                    'message' => "VET3: " . __('auth.verify_email_expired_token'),
                    'data'    => "VET3",
                ];
            }

            $this->usersRepository->update($user['id'], [
                'is_email_verified'  => true,
                'email_verified_at'  => now(),
                'token_expires_at'   => now(),
                'verification_token' => null,
                'password'           => Hash::make($password),
                'status'             => EnumsStatus::ACTIVE->value,
            ]);

            return [
                'status'  => true,
                'message' => "VET6: " . __('auth.verify_email_success'),
                'data'    => "VET6",
            ];
        } catch (\Exception $e) {
            Log::error('Verify email failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => "VET4: " . __('auth.error_email'),
                'data'    => "VET4",
            ];
        }
    }
}
