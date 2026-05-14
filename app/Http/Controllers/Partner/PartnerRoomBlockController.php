<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomBlockValidation;
use App\Models\Room;
use App\Services\RoomBlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Endpoint quản lý room block cho Partner Portal 360 (Phase 3).
 *
 * Mọi action đều giả định middleware `jwt.auth` + `role:partner` đã chạy
 * trước. Ownership cụ thể (room/block thuộc partner đăng nhập) được enforce
 * trong `RoomBlockService` qua `RoomBlockPolicy`.
 */
final class PartnerRoomBlockController extends Controller
{
    public function __construct(
        private readonly RoomBlockService $roomBlockService,
        private readonly RoomBlockValidation $validation,
    ) {
    }

    /**
     * Liệt kê room block của partner trong khoảng ngày.
     *
     * Query params: from, to (required), property_id, room_id (optional).
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->validation->listRoomBlockValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $partnerId  = (int) Auth::id();
        $propertyId = $request->filled('property_id') ? (int) $request->input('property_id') : null;
        $roomId     = $request->filled('room_id') ? (int) $request->input('room_id') : null;

        $roomIds = $this->resolvePartnerRoomIds($partnerId, $propertyId, $roomId);
        if ($roomIds === []) {
            return $this->successResponse([], __('room_block.messages.retrieved_successfully'));
        }

        $result = $this->roomBlockService->listForPartner(
            $roomIds,
            (string) $request->input('from'),
            (string) $request->input('to'),
        );

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Tạo room block mới.
     *
     * Trả 422 khi validate fail, 403 khi không sở hữu phòng, 409 khi conflict
     * (kèm payload chi tiết các booking/block đang chiếm range), 200 khi OK.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validation->createRoomBlockValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->roomBlockService->create([
            'room_id'    => (int) $request->input('room_id'),
            'start_date' => (string) $request->input('start_date'),
            'end_date'   => (string) $request->input('end_date'),
            'block_type' => (string) $request->input('block_type'),
            'reason'     => (string) $request->input('reason'),
            'note'       => $request->input('note'),
        ]);

        if (! $result['success']) {
            if (($result['code'] ?? null) === 'ROOM_BLOCK_CONFLICT') {
                return $this->errorResponse(
                    $result['message'],
                    'ROOM_BLOCK_CONFLICT',
                    HttpStatus::CONFLICT,
                    $result['data'],
                );
            }

            $status = $result['message'] === __('room_block.messages.unauthorized')
                ? HttpStatus::FORBIDDEN
                : HttpStatus::BAD_REQUEST;

            return $this->errorResponse($result['message'], null, $status);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Xoá 1 room block.
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->validation->deleteRoomBlockValidation($id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->roomBlockService->delete($id);
        if (! $result['success']) {
            $status = $result['message'] === __('room_block.messages.unauthorized')
                ? HttpStatus::FORBIDDEN
                : HttpStatus::BAD_REQUEST;

            return $this->errorResponse($result['message'], null, $status);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Trả về danh sách room id thuộc partner, có lọc theo property_id/room_id.
     *
     * @return array<int, int>
     */
    private function resolvePartnerRoomIds(int $partnerId, ?int $propertyId, ?int $roomId): array
    {
        return Room::query()
            ->whereHas('property', static function ($q) use ($partnerId, $propertyId): void {
                $q->where('user_id', $partnerId);
                if ($propertyId !== null) {
                    $q->where('id', $propertyId);
                }
            })
            ->when($roomId !== null, static fn ($q) => $q->where('id', $roomId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
