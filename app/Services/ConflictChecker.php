<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\RoomBlock;
use Illuminate\Support\Collection;

/**
 * Shared conflict checker cho Partner Portal 360 Phase 3.
 *
 * Quy tắc:
 *   - Hai khoảng `[a,b)` và `[c,d)` xung đột khi `a < d AND c < b`
 *     (end_date là exclusive — check-out trùng check-in là back-to-back, KHÔNG conflict).
 *   - Booking với status ∈ {CANCELLED, COMPLETED} bị loại trừ.
 *   - Room block luôn được tính (không có khái niệm "đã huỷ" cho room block;
 *     muốn bỏ thì xoá bản ghi).
 *
 * Dùng index `bookings(room_id, start_date, end_date, status)` và
 * `room_blocks(room_id, start_date, end_date)` cho hiệu năng. Tham số
 * `useLock = true` áp `lockForUpdate()` trên cả hai query — khi gọi từ
 * `BookingService::handleConfirmBooking` trong DB transaction sẽ pessimistic
 * lock theo `room_id` để chống race confirm song song.
 */
class ConflictChecker
{
    /**
     * Quy tắc giao khoảng `[a1,a2)` ∩ `[b1,b2)` ≠ ∅ ⇔ a1 < b2 ∧ b1 < a2.
     * Các tham số là chuỗi `Y-m-d` (so sánh từ điển = so sánh ngày). Tách
     * riêng để unit test mà không cần DB.
     */
    public static function intervalsOverlap(
        string $start1,
        string $end1,
        string $start2,
        string $end2,
    ): bool {
        return $start1 < $end2 && $start2 < $end1;
    }


    /**
     * Tìm tất cả booking + room block xung đột với khoảng yêu cầu.
     *
     * @return array{
     *     bookings: Collection<int, Booking>,
     *     blocks: Collection<int, RoomBlock>,
     *     hasConflict: bool
     * }
     */
    public function findConflicts(
        int $roomId,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null,
        ?int $excludeBlockId = null,
        bool $useLock = false,
    ): array {
        $bookingQuery = Booking::query()
            ->where('room_id', $roomId)
            ->whereNotIn('status', [
                BookingStatus::CANCELLED->value,
                BookingStatus::COMPLETED->value,
            ])
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate);

        if ($excludeBookingId !== null) {
            $bookingQuery->where('id', '!=', $excludeBookingId);
        }

        $blockQuery = RoomBlock::query()
            ->where('room_id', $roomId)
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate);

        if ($excludeBlockId !== null) {
            $blockQuery->where('id', '!=', $excludeBlockId);
        }

        if ($useLock) {
            $bookingQuery->lockForUpdate();
            $blockQuery->lockForUpdate();
        }

        $bookings = $bookingQuery->orderBy('start_date')->orderBy('id')->get();
        $blocks   = $blockQuery->orderBy('start_date')->orderBy('id')->get();

        return [
            'bookings'    => $bookings,
            'blocks'      => $blocks,
            'hasConflict' => $bookings->isNotEmpty() || $blocks->isNotEmpty(),
        ];
    }

    /**
     * Helper boolean — sugar cho callers chỉ cần biết có conflict hay không.
     */
    public function hasConflict(
        int $roomId,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null,
        ?int $excludeBlockId = null,
        bool $useLock = false,
    ): bool {
        return $this->findConflicts(
            $roomId,
            $startDate,
            $endDate,
            $excludeBookingId,
            $excludeBlockId,
            $useLock,
        )['hasConflict'];
    }
}
