<?php

namespace App\Services;

use App\Enums\Status as EnumsStatus;
use App\Enums\UserType;
use App\Models\User;
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
                'status'             => EnumsStatus::PENDING->value,
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

            $status = $user->role === UserType::PARTNER ? EnumsStatus::PENDING->value : EnumsStatus::ACTIVE->value;

            $this->usersRepository->update($user->id, [
                'is_email_verified'  => true,
                'email_verified_at'  => now(),
                'token_expires_at'   => null,
                'status'             => $status,
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

            if ((int) $user->status == EnumsStatus::PENDING->value && $user->role !== UserType::PARTNER) {
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

            $status = $user['role'] === UserType::PARTNER ? EnumsStatus::PENDING->value : EnumsStatus::ACTIVE->value;

            $this->usersRepository->update($user['id'], [
                'is_email_verified'  => true,
                'email_verified_at'  => now(),
                'token_expires_at'   => now(),
                'verification_token' => null,
                'password'           => Hash::make($password),
                'status'             => $status,
            ]);

            return [
                'status'  => true,
                'message' => "VET6: " . __('auth.verify_email_success'),
                'data'    => [
                    'status_code' => "VET6",
                    'role'        => $user['role'],
                ],
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
     * Handle the partner onboarding submission (Step 2 & 3).
     *
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function submitOnboarding(Request $request, $user): array
    {
        try {
            DB::beginTransaction();

            $partnerInfo = $this->partnerInfoRepository->findOneBy(['user_id' => $user->id], false);
            if (!$partnerInfo) {
                $partnerInfo = $this->partnerInfoRepository->create([
                    'user_id' => $user->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            $updateData = [
                'partner_type'        => $request->input('partner_type', $partnerInfo->partner_type ?? 'hotel'),
                'company_name'        => $request->input('company_name', $partnerInfo->company_name),
                'tax_code'           => $request->input('tax_code', $partnerInfo->tax_code),
                'representative_name' => $request->input('representative_name', $partnerInfo->representative_name),
                'province_id'         => $request->input('province_id', $partnerInfo->province_id),
                'ward_id'             => $request->input('ward_id', $partnerInfo->ward_id),
                'address'             => $request->input('address', $partnerInfo->address),
                'phone'               => $request->input('phone', $partnerInfo->phone),
                'website'             => $request->input('website', $partnerInfo->website),
                'description'         => $request->input('description', $partnerInfo->description),
                'bank_name'           => $request->input('bank_name', $partnerInfo->bank_name),
                'bank_account_number' => $request->input('bank_account_number', $partnerInfo->bank_account_number),
                'bank_account_holder' => $request->input('bank_account_holder', $partnerInfo->bank_account_holder),
                'updated_by'          => $user->id,
            ];

            $fileFields = [
                'id_card_front'        => 'id_card_front_file',
                'id_card_back'         => 'id_card_back_file',
                'business_license'     => 'business_license_file',
                'ownership_document'   => 'ownership_document_file',
                'bank_statement_image' => 'bank_statement_image_file',
            ];

            foreach ($fileFields as $dbColumn => $requestKey) {
                if ($request->hasFile($requestKey)) {
                    $file = $request->file($requestKey);
                    $path = $file->store("private/partners/{$user->id}", 'local');
                    $updateData[$dbColumn] = $path;
                }
            }

            $this->partnerInfoRepository->update($partnerInfo->id, $updateData);

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Nộp thông tin đăng ký thành công!',
                'data'    => $updateData,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Onboarding submit failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Handle the partner contract signature & submission (Step 4).
     *
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function signContract(Request $request, $user): array
    {
        try {
            DB::beginTransaction();

            $partnerInfo = $this->partnerInfoRepository->findOneBy(['user_id' => $user->id], false);
            if (!$partnerInfo) {
                return [
                    'status'  => false,
                    'message' => 'Không tìm thấy thông tin đối tác.',
                    'data'    => null,
                ];
            }

            $signatureBase64 = $request->input('signature_base64');
            if (empty($signatureBase64)) {
                return [
                    'status'  => false,
                    'message' => 'Chữ ký điện tử là bắt buộc.',
                    'data'    => null,
                ];
            }

            $imageParts = explode(";base64,", $signatureBase64);
            if (count($imageParts) > 1) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64Decoded = base64_decode($imageParts[1]);

                $signatureFilename = "signature_" . time() . "." . $imageType;
                $signaturePath = "private/partners/{$user->id}/{$signatureFilename}";

                \Illuminate\Support\Facades\Storage::disk('local')->put($signaturePath, $imageBase64Decoded);
            } else {
                return [
                    'status'  => false,
                    'message' => 'Định dạng chữ ký không hợp lệ.',
                    'data'    => null,
                ];
            }

            $contractPdfPath = "private/partners/{$user->id}/contract_" . time() . ".pdf";

            // Read signature base64 content
            $signatureBase64ForPdf = '';
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($signaturePath)) {
                $signatureData = \Illuminate\Support\Facades\Storage::disk('local')->get($signaturePath);
                $signatureBase64ForPdf = base64_encode($signatureData);
            }

            $html = view('pdf.contract', [
                'date' => now()->format('d/m/Y'),
                'company_name' => $partnerInfo->company_name,
                'representative_name' => $partnerInfo->representative_name,
                'tax_code' => $partnerInfo->tax_code,
                'address' => $partnerInfo->address,
                'signature_base64' => $signatureBase64ForPdf,
            ])->render();

            $mpdf = new \Mpdf\Mpdf([
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

            \Illuminate\Support\Facades\Storage::disk('local')->put($contractPdfPath, $pdfContent);

            $this->partnerInfoRepository->update($partnerInfo->id, [
                'contract_pdf_path' => $contractPdfPath,
                'updated_by'        => $user->id,
            ]);

            $this->usersRepository->update($user->id, [
                'status' => EnumsStatus::PENDING_APPROVAL->value,
            ]);

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Ký hợp đồng và hoàn tất nộp hồ sơ thành công!',
                'data'    => [
                    'contract_pdf_path' => $contractPdfPath,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract signing failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
    /**
     * Handle partner resubmitting onboarding after rejection.
     *
     * @param Request $request
     * @param \App\Models\User $user
     * @return array
     */
    public function resubmitOnboarding(Request $request, $user): array
    {
        try {
            DB::beginTransaction();

            $partnerInfo = $this->partnerInfoRepository->findOneBy(['user_id' => $user->id], false);
            if (!$partnerInfo) {
                return [
                    'status'  => false,
                    'message' => 'Không tìm thấy thông tin đối tác.',
                    'data'    => null,
                ];
            }

            // Update data and clear rejection reason
            $updateData = [
                'partner_type'        => $request->input('partner_type', $partnerInfo->partner_type),
                'company_name'        => $request->input('company_name', $partnerInfo->company_name),
                'tax_code'           => $request->input('tax_code', $partnerInfo->tax_code),
                'representative_name' => $request->input('representative_name', $partnerInfo->representative_name),
                'province_id'         => $request->input('province_id', $partnerInfo->province_id),
                'ward_id'             => $request->input('ward_id', $partnerInfo->ward_id),
                'address'             => $request->input('address', $partnerInfo->address),
                'phone'               => $request->input('phone', $partnerInfo->phone),
                'website'             => $request->input('website', $partnerInfo->website),
                'description'         => $request->input('description', $partnerInfo->description),
                'bank_name'           => $request->input('bank_name', $partnerInfo->bank_name),
                'bank_account_number' => $request->input('bank_account_number', $partnerInfo->bank_account_number),
                'bank_account_holder' => $request->input('bank_account_holder', $partnerInfo->bank_account_holder),
                'rejection_reason'    => null, // Clear rejection reason
                'updated_by'          => $user->id,
            ];

            $fileFields = [
                'id_card_front'        => 'id_card_front_file',
                'id_card_back'         => 'id_card_back_file',
                'business_license'     => 'business_license_file',
                'ownership_document'   => 'ownership_document_file',
                'bank_statement_image' => 'bank_statement_image_file',
            ];

            foreach ($fileFields as $dbColumn => $requestKey) {
                if ($request->hasFile($requestKey)) {
                    $file = $request->file($requestKey);
                    $path = $file->store("private/partners/{$user->id}", 'local');
                    $updateData[$dbColumn] = $path;
                }
            }

            $this->partnerInfoRepository->update($partnerInfo->id, $updateData);

            // Change user status back to PENDING_APPROVAL
            $this->usersRepository->update($user->id, [
                'status' => EnumsStatus::PENDING_APPROVAL->value,
            ]);

            // Notify all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Partner nộp lại hồ sơ',
                    'message' => "Đối tác {$partnerInfo->company_name} đã chỉnh sửa và nộp lại hồ sơ.",
                    'type' => 'system',
                ]);
            }

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Nộp lại hồ sơ thành công! Vui lòng chờ quản trị viên duyệt.',
                'data'    => $updateData,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Onboarding resubmit failed: ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
