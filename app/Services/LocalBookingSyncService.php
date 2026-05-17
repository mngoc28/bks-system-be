<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\PricePackageRepository\PricePackageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * T6: merge đơn đặt lưu cục bộ lên server (dedupe fingerprint + khớp slot đã tạo trước đó).
 */
final class LocalBookingSyncService
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly PricePackageRepositoryInterface $pricePackageRepository,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{mapped: array<int, array{local_id: string, server_booking_id: int, action: string}>}
     */
    public function sync(User $user, array $items): array
    {
        $userId = (int) $user->id;
        $mapped  = [];

        DB::transaction(function () use ($user, $userId, $items, &$mapped): void {
            $resolvedInBatch = [];

            foreach ($items as $index => $item) {
                $localId = (string) $item['local_id'];
                $fpIn    = (string) $item['fingerprint'];
                $roomId  = (int) $item['room_id'];
                $start   = Carbon::parse((string) $item['start_date'])->format('Y-m-d');
                $end     = Carbon::parse((string) $item['end_date'])->format('Y-m-d');

                $emailForFp = $this->emailForItem($user, $item);
                $expectedFp = $this->computeFingerprint($roomId, $start, $end, $emailForFp);
                if (! hash_equals($expectedFp, $fpIn)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.fingerprint" => [__('booking.sync_local.fingerprint_mismatch')],
                    ]);
                }

                if (isset($resolvedInBatch[$fpIn])) {
                    $mapped[] = [
                        'local_id'          => $localId,
                        'server_booking_id' => $resolvedInBatch[$fpIn],
                        'action'            => 'linked',
                    ];
                    continue;
                }

                $existing = $this->findByFingerprintLocked($userId, $fpIn);
                if ($existing === null) {
                    $existing = $this->findBySlotLocked($userId, $roomId, $start, $end);
                }

                if ($existing !== null) {
                    if (
                        $existing->client_fingerprint !== null
                        && $existing->client_fingerprint !== ''
                        && ! hash_equals((string) $existing->client_fingerprint, $fpIn)
                    ) {
                        throw ValidationException::withMessages([
                            "items.{$index}.fingerprint" => [__('booking.sync_local.slot_fingerprint_conflict')],
                        ]);
                    }

                    if ($existing->client_fingerprint === null || $existing->client_fingerprint === '') {
                        $source = ($existing->source !== null && (string) $existing->source !== '')
                            ? (string) $existing->source
                            : 'local_sync';
                        $this->bookingRepository->update((int) $existing->id, [
                            'client_fingerprint' => $fpIn,
                            'client_local_id'    => $localId,
                            'source'             => $source,
                        ]);
                    } elseif (($existing->client_local_id ?? '') === '') {
                        $this->bookingRepository->update((int) $existing->id, [
                            'client_local_id' => $localId,
                        ]);
                    }

                    $resolvedInBatch[$fpIn] = (int) $existing->id;
                    $mapped[]               = [
                        'local_id'          => $localId,
                        'server_booking_id' => (int) $existing->id,
                        'action'            => 'linked',
                    ];
                    continue;
                }

                $priceId = $this->resolvePriceId($roomId, isset($item['price_id']) ? (int) $item['price_id'] : null);

                $inner = Request::create('/internal/stay/sync-local', 'POST', [
                    'user_id'              => $userId,
                    'room_id'              => $roomId,
                    'start_date'           => $start,
                    'end_date'             => $end,
                    'price_id'             => $priceId,
                    'note'                 => __('booking.sync_local.note_auto'),
                    'status'               => BookingStatus::PENDING->value,
                    'client_local_id'      => $localId,
                    'client_fingerprint'   => $fpIn,
                    'source'               => 'local_sync',
                ]);

                $result = $this->bookingService->handleCreateBooking($inner);
                if (! $result['success'] || ! ($result['data'] instanceof Booking)) {
                    throw ValidationException::withMessages([
                        "items.{$index}" => [$result['message'] ?: __('booking.sync_local.create_failed')],
                    ]);
                }

                /** @var Booking $created */
                $created                    = $result['data'];
                $resolvedInBatch[$fpIn]     = (int) $created->id;
                $mapped[]                   = [
                    'local_id'          => $localId,
                    'server_booking_id' => (int) $created->id,
                    'action'            => 'created',
                ];
            }
        });

        return ['mapped' => $mapped];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function emailForItem(User $user, array $item): string
    {
        if (isset($item['email']) && is_string($item['email']) && trim($item['email']) !== '') {
            return $this->normalizeEmail($item['email']);
        }

        return $this->normalizeEmail((string) $user->email);
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function computeFingerprint(int $roomId, string $startYmd, string $endYmd, string $normalizedEmail): string
    {
        $payload = $roomId . '|' . $startYmd . '|' . $endYmd . '|' . $normalizedEmail;

        return hash('sha256', $payload);
    }

    private function findByFingerprintLocked(int $userId, string $fingerprint): ?Booking
    {
        $row = Booking::query()
            ->where('user_id', $userId)
            ->where('client_fingerprint', $fingerprint)
            ->lockForUpdate()
            ->first();

        return $row instanceof Booking ? $row : null;
    }

    private function findBySlotLocked(int $userId, int $roomId, string $startYmd, string $endYmd): ?Booking
    {
        $row = Booking::query()
            ->where('user_id', $userId)
            ->where('room_id', $roomId)
            ->whereDate('start_date', $startYmd)
            ->whereDate('end_date', $endYmd)
            ->where('status', '!=', BookingStatus::CANCELLED->value)
            ->lockForUpdate()
            ->first();

        return $row instanceof Booking ? $row : null;
    }

    private function resolvePriceId(int $roomId, ?int $requestedPriceId): int
    {
        if (
            $requestedPriceId !== null
            && $this->bookingRepository->checkPriceExistsForRoom($roomId, $requestedPriceId)
        ) {
            return $requestedPriceId;
        }

        $pkg = $this->pricePackageRepository->getDefaultPriceOfRoom($roomId);
        if ($pkg === null || empty($pkg->price_id)) {
            throw ValidationException::withMessages([
                'items' => [__('booking.sync_local.price_not_found')],
            ]);
        }

        return (int) $pkg->price_id;
    }
}
