<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomPrice;
use App\Services\DynamicDepositPolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DynamicDepositPolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_short_term_weekend_uses_fifty_percent_not_configured_deposit(): void
    {
        $room = Room::query()->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->where('unit', 'night')->first()
            ?? RoomPrice::query()->where('unit', 'night')->firstOrFail();

        $price->update(['deposit_amount' => 9_000_000, 'price' => 810_000]);

        $service = app(DynamicDepositPolicyService::class);
        $result = $service->calculateRequiredDeposit(
            $room->fresh(),
            $price->fresh(),
            '2026-07-11',
            '2026-07-12',
        );

        $this->assertTrue($result['required']);
        $this->assertSame(405_000.0, $result['amount']);
    }

    public function test_long_term_uses_configured_security_deposit(): void
    {
        $room = Room::query()->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'month')->firstOrFail();
        $price->update(['deposit_amount' => 9_000_000, 'price' => 18_000_000]);

        $service = app(DynamicDepositPolicyService::class);
        $result = $service->calculateRequiredDeposit(
            $room->fresh(),
            $price->fresh(),
            now()->addDays(2)->format('Y-m-d'),
            now()->addDays(62)->format('Y-m-d'),
        );

        $this->assertTrue($result['required']);
        $this->assertSame(9_000_000.0, $result['amount']);
    }
}
