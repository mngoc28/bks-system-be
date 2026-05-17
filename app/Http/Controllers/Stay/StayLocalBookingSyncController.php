<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stay\StaySyncLocalBookingsRequest;
use App\Services\LocalBookingSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class StayLocalBookingSyncController extends Controller
{
    public function __construct(
        private readonly LocalBookingSyncService $localBookingSyncService,
    ) {
    }

    public function sync(StaySyncLocalBookingsRequest $request): JsonResponse
    {
        $user = Auth::user();
        if ($user === null) {
            return $this->errorResponse(__('auth.unauthorized'), null, HttpStatus::UNAUTHORIZED);
        }

        if (($user->role ?? '') !== UserType::USER) {
            return $this->forbiddenResponse(__('booking.sync_local.forbidden_role'));
        }

        try {
            $items = $request->validated()['items'];
            $out    = $this->localBookingSyncService->sync($user, $items);

            return $this->successResponse($out, __('booking.sync_local.success'));
        } catch (ValidationException $e) {
            return $this->validateError($e->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
    }
}
