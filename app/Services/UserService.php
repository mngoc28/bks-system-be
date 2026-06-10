<?php

namespace App\Services;

use App\Enums\HttpStatus;
use App\Enums\Status;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\Validations\UserValidation;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UserService
{
    protected $userRepository;
    protected $validation;
    protected $cloudinaryService;

    public function __construct(
        UsersRepositoryInterface $userRepository,
        UserValidation $validation,
        CloudinaryService $cloudinaryService
    ) {
        $this->userRepository = $userRepository;
        $this->validation = $validation;
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Normalize avatar path to an absolute URL for API responses.
     *
     * @param string|null $avatar
     * @return string|null
     */
    private function resolveAvatarUrl(?string $avatar): ?string
    {
        if (! $avatar) {
            return null;
        }

        if (preg_match('/^(https?:)?\/\//i', $avatar)) {
            return $avatar;
        }

        $baseUrl = rtrim((string) config('const.CLOUDINARY_HEADER_IMAGE_URL'), '/');
        $path = '/' . ltrim($avatar, '/');

        return $baseUrl . $path;
    }

    /**
     * Handle getting all users.
     *
     * @param mixed $request
     * @return array
     */
    public function handleGetAllUsers($request): array
    {
        try {
            $users = $this->userRepository->getAll($request);

            if ($users) {
                $users->getCollection()->transform(function ($user) {
                    $user->avatar = $this->resolveAvatarUrl($user->avatar);
                    return $user;
                });
            }

            return [
                'success' => true,
                'data'    => $users,
                'code'    => HttpStatus::OK->value,
            ];
        } catch (Exception $e) {
            Log::error('Get all users error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => __('user.get_user_error'),
                'code'    => HttpStatus::INTERNAL_SERVER_ERROR->value,
            ];
        }
    }

    /**
     * Admin creates user/partner account
     * @param Request $request
     * @return object|null
     */
    public function handleCreateUser($request): object|null
    {
        try {
            // Use base repository create method
            return $this->userRepository->create([
                'name'       => $request->input('name'),
                'email'      => $request->input('email'),
                'role'       => $request->input('role', 'user'),
                'phone'      => $request->input('phone'),
                'password'   => Hash::make($request->input('password')),
                'status'     => $request->input('status', '1'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin create user failed: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Handle getting user profile data.
     *
     * @param int $Id
     * @return mixed
     */
    public function handleGetUserById(int $Id)
    {
        try {
            $user = $this->userRepository->find($Id);
            if (! $user) {
                return [false, __('user.not_found')];
            }

            if ($user->role === 'partner') {
                $user->load(['partnerInfo.province', 'partnerInfo.ward']);
            }

            return [
                true,
                [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role ?? null,
                    'phone'      => $user->phone ?? null,
                    'status'     => $user->status,
                    'avatar'     => $this->resolveAvatarUrl($user->avatar),
                    'id_avatar'  => $user->id_avatar,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'partner_info' => $user->partnerInfo ?? null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());
            return [false, __('user.get_user_error')];
        }
    }

    /**
     * Summary of handleUpdateProfile
     * @param int $Id
     * @param array $data
     * @return array<array|array{email: mixed, id: mixed, name: mixed, phone: mixed, role: mixed|bool|string|null>}
     */
    public function handleUpdate(int $Id, array $data): array
    {
        try {
            if (empty(array_filter($data))) {
                return [false, __('user.update_no_data')];
            }

            $allowedFields = ['name', 'email', 'phone', 'role', 'avatar', 'id_avatar'];
            $data          = array_intersect_key($data, array_flip($allowedFields));
            $data          = array_filter($data, fn($value) => $value !== null && $value !== '');

            if (empty($data)) {
                return [false, __('user.update_no_data')];
            }

            $user = $this->userRepository->find($Id);
            if (! $user) {
                return [false, __('user.update_error')];
            }

            $this->userRepository->update($Id, $data);

            $updatedUser = $this->userRepository->find($Id);
            return [
                true,
                [
                    'id'    => $updatedUser->id,
                    'name'  => $updatedUser->name,
                    'email' => $updatedUser->email ?? null,
                    'role'  => $updatedUser->role ?? null,
                    'phone' => $updatedUser->phone ?? null,
                    'avatar' => $this->resolveAvatarUrl($updatedUser->avatar),
                    'id_avatar' => $updatedUser->id_avatar ?? null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());
            return [false, __('user.get_user_error')];
        }
    }

    /**
     * Update account status (activate / block / unblock) — admin only.
     *
     * @return array{0: bool, 1: array<string, mixed>|string}
     */
    public function handleUpdateStatus(int $id, int $status): array
    {
        try {
            if (! in_array($status, [Status::ACTIVE->value, Status::BLOCKED->value], true)) {
                return [false, __('user.status_invalid')];
            }

            $user = $this->userRepository->find($id);
            if (! $user) {
                return [false, __('user.not_found')];
            }

            $actor = Auth::user();
            if ($actor && (int) $actor->id === $id) {
                return [false, __('user.cannot_update_own_status')];
            }

            if ($user->role === 'admin') {
                return [false, __('user.cannot_change_admin_status')];
            }

            $currentStatus = (int) $user->status;

            if ($user->role === 'partner' && $currentStatus === Status::PENDING_APPROVAL->value) {
                return [false, __('user.partner_use_approval_flow')];
            }

            $allowed = match ($status) {
                Status::ACTIVE->value => in_array($currentStatus, [Status::PENDING->value, Status::BLOCKED->value], true),
                Status::BLOCKED->value => $currentStatus === Status::ACTIVE->value,
            };

            if (! $allowed) {
                return [false, __('user.status_transition_invalid')];
            }

            $this->userRepository->update($id, [
                'status' => $status,
                'updated_by' => $actor?->id,
            ]);

            $updatedUser = $this->userRepository->find($id);

            return [
                true,
                [
                    'id' => $updatedUser->id,
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'role' => $updatedUser->role,
                    'status' => (int) $updatedUser->status,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Update user status error: ' . $e->getMessage());

            return [false, __('user.update_error')];
        }
    }

    /**
     * Summary of handleChangePassword
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array<array|bool|string|null>
     */
    public function handleChangePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            $user = $this->userRepository->find($userId);
            if (! $user) {
                return [false, __('user.not_found')];
            }
            if (! Hash::check($currentPassword, $user->password)) {
                return [false, __('user.current_password_incorrect')];
            }

            $this->userRepository->update($userId, ['password' => Hash::make($newPassword)]);
            return [true, null];
        } catch (Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return [false, __('user.get_user_error')];
        }
    }

    /**
     * Handle reset password by admin (no current password required)
     *
     * @param int $userId
     * @param string $newPassword
     * @return array
     */
    public function handleResetPassword(int $userId, string $newPassword): array
    {
        try {
            $user = $this->userRepository->find($userId);
            if (! $user) {
                return [false, __('user.not_found')];
            }

            $this->userRepository->update($userId, ['password' => Hash::make($newPassword)]);
            return [true, null];
        } catch (Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return [false, __('user.reset_password_error')];
        }
    }

    /**
     * Summary of handleDelete
     * @param int $Id
     * @return array<array|bool|string|null>
     */
    public function handleDelete(int $Id): array
    {
        try {
            $user = $this->userRepository->find($Id);

            $deleted = $this->userRepository->delete($Id);

            if ($deleted === false) {
                Log::warning("Delete user failed (repository returned false) for id={$Id}");
                return [false, __('user.delete_failed')];
            }

            return [true, null];
        } catch (Exception $e) {
            Log::error('Delete user error: ' . $e->getMessage());
            return [false, __('user.get_user_error')];
        }
    }
    /**
     * Summary of getUserById
     * @param int $id
     * @return object|null
     */
    public function getUserById(int $id): ?object
    {
        try {
            return $this->userRepository->find($id);
        } catch (Exception $e) {
            Log::error('Get user by id error: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Summary of uploadAvatar
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function uploadAvatar(array $data): array
    {
        try {
            $userId = (int) $data['user_id'];
            $updated = $this->userRepository->update($userId, [
                'avatar' => $data['avatar'],
                'id_avatar' => $data['id_avatar']
            ]);

            // Check if update was successful
            if (!$updated) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('user.avatar_update_failed'),
                ];
            }
            return [
                'success' => true,
                'data' => $updated,
                'message' => __('user.avatar_update_success'),
            ];
        } catch (Exception $e) {
            Log::error('User avatar update error: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('user.avatar_update_failed'),
            ];
        }
    }

    /**
     * Handle upload avatar
     * @param Request $request
     * @param int $userId
     * @return array
     */
    public function handleUploadAvatar(Request $request, int $userId): array
    {
        DB::beginTransaction();

        try {
            $request->merge(['user_id' => $userId]);

            $targetUser = $this->getUserById($userId);

            // Delete old avatar if exists
            if ($targetUser->id_avatar) {
                $deleteResult = $this->cloudinaryService->deleteImage($targetUser->id_avatar);
                if (!$deleteResult['success']) {
                    return [
                        'success' => false,
                        'message' => __('user.avatar_delete_failed'),
                        'code' => HttpStatus::BAD_REQUEST,
                    ];
                }
            }

            // upload new avatar to Cloudinary
            $files = $request->file('image');
            $uploadResult = $this->cloudinaryService->uploadImage(
                $files,
                'users/' . $targetUser->id
            );
            if (!$uploadResult['success']) {
                return [
                    'success' => false,
                    'message' => __('user.avatar_upload_failed'),
                    'code' => HttpStatus::BAD_REQUEST,
                ];
            }

            // save avatar info into users table
            $result = $this->uploadAvatar([
                'user_id' => $targetUser->id,
                'avatar' => $uploadResult['url'],
                'id_avatar' => $uploadResult['public_id'],
            ]);

            if (!$result['success']) {
                // Delete uploaded image from Cloudinary if DB save failed
                $this->cloudinaryService->deleteImage($uploadResult['public_id']);
                return $result;
            }

            DB::commit();

            // Attach full URL for response
            $responseData = $uploadResult;
            $responseData['url'] = $this->resolveAvatarUrl($uploadResult['url']);

            return [
                'success' => true,
                'data' => $responseData,
                'message' => __('user.avatar_upload_success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();

            // If upload succeeded but DB failed, delete the uploaded image
            if (isset($uploadResult) && $uploadResult['success']) {
                $this->cloudinaryService->deleteImage($uploadResult['public_id']);
            }

            Log::error('Avatar upload failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('user.avatar_upload_failed'),
                'code' => HttpStatus::INTERNAL_SERVER_ERROR,
            ];
        }
    }
}
