<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Chi tiết đơn đặt phòng trong kỳ đối soát.
 */
final class SettlementLineItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settlement_line_items';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checkout_date'     => 'date',
        'room_gmv'          => 'float',
        'services_gmv'      => 'float',
        'total_gmv'         => 'float',
        'commission_amount' => 'float',
        'snapshot_status'   => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Get the settlement period.
     *
     * @return BelongsTo
     */
    public function settlementPeriod(): BelongsTo
    {
        return $this->belongsTo(PartnerSettlementPeriod::class, 'settlement_period_id');
    }

    /**
     * Get the booking.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
