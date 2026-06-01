<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Repositories\RoomBlockRepository\RoomBlockRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service tổng hợp lịch booking + room block cho Partner Portal 360 (Phase 3).
 *
 * Cache 30s qua key `calendar:{partnerId}:v{version}:{scope}:{from}:{to}`.
 * Khi sự kiện invalidation tới (BookingConfirmed/Cancelled/RoomBlockChanged)
 * chỉ cần `bumpVersion(partnerId)` để các key đang còn hạn bị bỏ qua → request
 * tiếp theo recompute. Cách này hoạt động trên mọi cache driver, không phụ
 * thuộc Redis tags.
 */
final class PartnerCalendarService
{
    public const CACHE_TTL_SECONDS = 30;
    public const MAX_RANGE_DAYS    = 31;

    public function __construct(
        private readonly RoomBlockRepositoryInterface $blockRepository,
    ) {
    }

    /**
     * Trả `{bookings, blocks}` của partner trong `[from, to]`.
     *
     * @return array{
     *     bookings: array<int, array<string, mixed>>,
     *     blocks: array<int, array<string, mixed>>,
     *     property_id: int|string|null,
     *     room_id: int|null,
     *     from: string,
     *     to: string,
     *     cached_at: string
     * }
     */
    public function getCalendar(
        int $partnerId,
        ?int $propertyId,
        ?int $roomId,
        string $fromDate,
        string $toDate,
    ): array {
        $scope   = $propertyId !== null ? (string) $propertyId : 'all';
        $roomKey = $roomId !== null ? (string) $roomId : 'any';
        $cacheKey = sprintf(
            'calendar:%d:v%d:%s:%s:%s:%s',
            $partnerId,
            $this->getVersion($partnerId),
            $scope,
            $roomKey,
            $fromDate,
            $toDate,
        );

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn () => $this->compute($partnerId, $propertyId, $roomId, $fromDate, $toDate),
        );
    }

    /**
     * Bumps phiên bản cache cho partner — buộc các key cũ bị bỏ qua.
     */
    public function bumpVersion(int $partnerId): void
    {
        $key = $this->versionKey($partnerId);
        try {
            Cache::increment($key);
        } catch (\Throwable $e) {
            Cache::put($key, $this->currentVersionFallback(), now()->addDays(7));
        }
    }

    private function compute(
        int $partnerId,
        ?int $propertyId,
        ?int $roomId,
        string $fromDate,
        string $toDate,
    ): array {
        $roomIds = $this->resolvePartnerRoomIds($partnerId, $propertyId, $roomId);
        if ($roomIds === []) {
            return [
                'bookings'    => [],
                'blocks'      => [],
                'property_id' => $propertyId,
                'room_id'     => $roomId,
                'from'        => $fromDate,
                'to'          => $toDate,
                'cached_at'   => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
            ];
        }

        $bookings = Booking::query()
            ->with([
                'room:id,property_id,title,room_number',
                'room.property:id,name',
                'user:id,name,phone',
                'price:id,price',
            ])
            ->whereIn('room_id', $roomIds)
            ->whereNotIn('status', [BookingStatus::CANCELLED->value])
            ->where('start_date', '<=', $toDate)
            ->where('end_date', '>=', $fromDate)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get([
                'id', 'room_id', 'user_id', 'price_id', 'start_date', 'end_date',
                'status', 'stay_status', 'confirmed_at', 'created_at', 'note',
            ]);

        $blocks = $this->blockRepository->listForRoomsInRange($roomIds, $fromDate, $toDate);

        return [
            'bookings'    => $bookings->map(fn (Booking $b) => $this->serializeBooking($b))->values()->all(),
            'blocks'      => $blocks->map(fn (RoomBlock $block) => $this->serializeBlock($block))->values()->all(),
            'property_id' => $propertyId,
            'room_id'     => $roomId,
            'from'        => $fromDate,
            'to'          => $toDate,
            'cached_at'   => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
        ];
    }

    /**
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

    /**
     * @return array<string, mixed>
     */
    private function serializeBooking(Booking $booking): array
    {
        $start = optional($booking->start_date)->format('Y-m-d')
            ?? (string) $booking->getRawOriginal('start_date');
        $end = optional($booking->end_date)->format('Y-m-d')
            ?? (string) $booking->getRawOriginal('end_date');

        $room       = $booking->relationLoaded('room') ? $booking->room : null;
        $user       = $booking->relationLoaded('user') ? $booking->user : null;
        $totalAmount = BookingStayAmountCalculator::computeRoomStayTotalForBooking($booking);

        $roomLabel = $room?->room_number ?: ($room?->title ?? null);

        return [
            'id'             => (int) $booking->id,
            'room_id'        => (int) $booking->room_id,
            'start_date'     => $start,
            'end_date'       => $end,
            'status'         => (int) $booking->status,
            'stay_status'    => $booking->stay_status,
            'confirmed_at'   => $booking->confirmed_at,
            'property_id'    => $room ? (int) $room->property_id : null,
            'property_name'  => $room?->property?->name,
            'room_label'     => $roomLabel,
            'room_title'     => $room?->title,
            'guest_name'     => $user?->name,
            'guest_phone'    => $user?->phone,
            'total_amount'   => $totalAmount,
            'note'           => $booking->note,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBlock(RoomBlock $block): array
    {
        return [
            'id'         => (int) $block->id,
            'room_id'    => (int) $block->room_id,
            'start_date' => optional($block->start_date)->format('Y-m-d'),
            'end_date'   => optional($block->end_date)->format('Y-m-d'),
            'block_type' => (string) $block->block_type,
            'reason'     => (string) $block->reason,
            'note'       => $block->note,
        ];
    }

    private function getVersion(int $partnerId): int
    {
        $key = $this->versionKey($partnerId);
        $value = Cache::get($key);
        if ($value === null) {
            Cache::put($key, 1, now()->addDays(7));
            return 1;
        }

        return (int) $value;
    }

    private function versionKey(int $partnerId): string
    {
        return sprintf('calendar:%d:version', $partnerId);
    }

    private function currentVersionFallback(): int
    {
        return (int) Carbon::now()->getTimestamp();
    }
}
