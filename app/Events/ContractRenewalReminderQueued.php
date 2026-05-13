<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when the daily scheduler tags a long-term contract as approaching
 * expiry. The Partner Alert Center subscribes to refresh its "Contract sắp
 * hết hạn" tile in near-realtime, and listeners can also use it to clear
 * caches.
 */
final class ContractRenewalReminderQueued implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Contract $contract;
    public int $partnerId;
    public ?int $propertyId;
    public string $bookingEndDate;

    public function __construct(
        Contract $contract,
        int $partnerId,
        ?int $propertyId,
        string $bookingEndDate,
    ) {
        $this->contract       = $contract->withoutRelations();
        $this->partnerId      = $partnerId;
        $this->propertyId     = $propertyId;
        $this->bookingEndDate = $bookingEndDate;
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('partner.' . $this->partnerId)];
        if ($this->propertyId !== null) {
            $channels[] = new PrivateChannel('property.' . $this->propertyId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'contract.renewal_reminder';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id'              => (int) $this->contract->id,
            'partner_id'      => $this->partnerId,
            'property_id'     => $this->propertyId,
            'booking_id'      => (int) $this->contract->booking_id,
            'booking_end_date'=> $this->bookingEndDate,
            'contract_type'   => (string) $this->contract->contract_type,
            'reminded_at'     => optional($this->contract->renewal_reminder_at)->toIso8601String(),
        ];
    }
}
