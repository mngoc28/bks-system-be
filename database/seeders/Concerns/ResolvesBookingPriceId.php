<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ResolvesBookingPriceId
{
    private const LONG_STAY_DAYS_THRESHOLD = 30;

    /**
     * @param array<int, list<array{id: int, unit: string}>> $pricesByRoomId
     */
    protected function resolvePriceIdForStay(
        int $roomId,
        string $startDate,
        string $endDate,
        array $pricesByRoomId,
    ): ?int {
        $prices = $pricesByRoomId[$roomId] ?? [];

        if ($prices === []) {
            return null;
        }

        $stayDays = $this->calculateStayDays($startDate, $endDate);
        $preferMonth = $stayDays >= self::LONG_STAY_DAYS_THRESHOLD;

        if (!$preferMonth) {
            foreach ($prices as $price) {
                if ($price['unit'] === 'night') {
                    return $price['id'];
                }
            }

            foreach ($prices as $price) {
                if ($price['unit'] === 'month') {
                    return $price['id'];
                }
            }

            return $prices[0]['id'] ?? null;
        }

        foreach ($prices as $price) {
            if ($price['unit'] === 'month') {
                return $price['id'];
            }
        }

        foreach ($prices as $price) {
            if ($price['unit'] === 'night') {
                return $price['id'];
            }
        }

        return $prices[0]['id'];
    }

    protected function calculateStayDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return max(1, (int) $start->diffInDays($end));
    }

    /**
     * @return array<int, list<array{id: int, unit: string}>>
     */
    protected function loadRoomPricesIndexedByRoomId(): array
    {
        $indexed = [];

        $rows = DB::table('room_prices')
            ->select('id', 'room_id', 'unit')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $roomId = (int) $row->room_id;
            $indexed[$roomId][] = [
                'id' => (int) $row->id,
                'unit' => (string) $row->unit,
            ];
        }

        return $indexed;
    }
}
