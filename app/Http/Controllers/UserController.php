<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\UserValidation;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends \App\Http\Controllers\Controller
{
    protected $userService;
    protected $validation;
    protected $cloudinaryService;

    public function __construct(
        UserService $userService,
        UserValidation $validation
    ) {
        $this->userService = $userService;
        $this->validation  = $validation;
    }

    /**
     * Summary of index
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('index', Auth::user());

        $validator = $this->validation->validateGetAll($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $this->userService->handleGetAllUsers($request);

        if ($data['success']) {
            return $this->successResponse(
                $data['data'],
                __('user.get_users_success'),
            );
        }

        return $this->errorResponse(
            __('user.get_users_error'),
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Summary of create
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Check authorization (admin only)
        $this->authorize('store', Auth::user());

        $validator = $this->validation->validateCreate($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $this->userService->handleCreateUser($request);

        if ($data) {
            return $this->createdResponse(
                [
                    'name'       => $data->name,
                    'email'      => $data->email,
                    'role'       => $data->role,
                    'phone'      => $data->phone,
                    'status'     => $data->status,
                ],
                __('user.create_success')
            );
        }

        return $this->errorResponse(
            __('user.create_error'),
            null,
            HttpStatus::BAD_REQUEST
        );
    }

    /**
     * Summary of show
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id = null): JsonResponse
    {
        // If no user is provided (for profile route), use the authenticated user
        if ($id === null) {
            $id = Auth::id();
        }

        $targetUser = $this->userService->getUserById($id);
        if (!$targetUser) {
            return $this->errorResponse(
                __('user.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        $this->authorize('view', $targetUser);

        [$status, $dataResult] = $this->userService->handleGetUserById($id);
        if ($status === true) {
            return $this->successResponse(
                $dataResult,
                __('user.get_user_success'),
            );
        }

        return $this->errorResponse($dataResult, null, HttpStatus::NOT_FOUND);
    }

    /**
     * Summary of updateProfile
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateProfile(Request $request, $id = null): JsonResponse
    {
        // If no ID is provided, use the authenticated user's ID to update their own profile
        if ($id === null) {
            $id = Auth::id();
        }

        $targetUser = Auth::user();
        if (! $targetUser) {
            return $this->errorResponse(
                __('user.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        $this->authorize('updateProfile', $targetUser);

        $validated = $this->validation->validateUpdate($request);
        if ($validated->fails()) {
            return $this->errorResponse(
                $validated->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $request->all();
        [$status, $dataResult] = $this->userService->handleUpdate($id, $data);

        if ($status === true) {
            return $this->successResponse($dataResult, __('user.update_success'));
        }

        return $this->errorResponse($dataResult, null, HttpStatus::NOT_FOUND);
    }

    /**
     * Update user by id (admin only, for apiResource)
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Check authorization (admin only)
        $this->authorize('update', Auth::user());

        $validated = $this->validation->validateUpdate($request);
        if ($validated->fails()) {
            return $this->errorResponse(
                $validated->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $data = $request->only(['name', 'email', 'phone', 'role', 'avatar', 'id_avatar']);
        [$status, $dataResult] = $this->userService->handleUpdate($id, $data);

        if ($status === true) {
            return $this->successResponse($dataResult, __('user.update_success'));
        }

        return $this->errorResponse($dataResult, HttpStatus::NOT_FOUND);
    }

    /**
     * Summary of changePassword
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function changePassword(Request $request, $id = null): JsonResponse
    {
        // If no ID is provided, use the authenticated user's ID
        if ($id === null) {
            $id = Auth::id();
        }

        $targetUser = $this->userService->getUserById($id);
        if (!$targetUser) {
            return $this->errorResponse(
                __('user.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        $this->authorize('changePassword', $targetUser);

        $validator = $this->validation->validateChangePassword($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $currentPassword       = $request->input('current_password');
        $newPassword           = $request->input('new_password');
        [$status, $dataResult] = $this->userService->handleChangePassword($id, $currentPassword, $newPassword);

        if ($status === true) {
            return $this->successResponse(null, __('user.password_updated'));
        }

        return $this->errorResponse($dataResult, null, HttpStatus::NOT_FOUND);
    }

    /**
     * Reset password by admin (no current password required)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        // Check if user exists
        $targetUser = $this->userService->getUserById($id);
        if (!$targetUser) {
            return $this->errorResponse(
                __('user.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        // Check authorization (admin only)
        $this->authorize('resetPassword', $targetUser);

        $validator = $this->validation->validateResetPassword($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $newPassword = $request->input('new_password');
        [$status, $dataResult] = $this->userService->handleResetPassword($id, $newPassword);

        if ($status === true) {
            return $this->successResponse(null, __('user.password_reset_success'));
        }

        return $this->errorResponse($dataResult, null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Summary of delete
     * @param int id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $targetUser = $this->userService->getUserById($id);
        if (!$targetUser) {
            return $this->errorResponse(
                __('user.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }
        // Check authorization (admin only)
        $this->authorize('destroy', $targetUser);

        [$status, $dataResult] = $this->userService->handleDelete($id);
        if ($status === true) {
            return $this->successResponse(
                $dataResult,
                __('user.delete_success')
            );
        }

        return $this->errorResponse(
            $dataResult,
            null,
            HttpStatus::NOT_FOUND
        );
    }

    /**
     * Summary of uploadAvatar
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request, $userId): JsonResponse
    {
        $userId = $request->route('id');

        // If uploading for another user, check admin permission
        if ($userId != Auth::id()) {
            $this->authorize('update', Auth::user());
        }

        $request->merge(['user_id' => $userId]);
        $validator = $this->validation->validateUploadAvatar($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->userService->handleUploadAvatar($request, $userId);

        if ($result['success']) {
            return $this->successResponse(
                $result['data'],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            null,
            $result['code'] ?? HttpStatus::BAD_REQUEST
        );
    }
}
