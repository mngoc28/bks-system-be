<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kỳ đối soát của đối tác (Model A).
 */
final class PartnerSettlementPeriod extends Model
{
    use HasFactory;

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_ISSUED   = 'issued';
    public const STATUS_PAID     = 'paid';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_CLOSED   = 'closed';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ISSUED,
        self::STATUS_PAID,
        self::STATUS_DISPUTED,
        self::STATUS_CLOSED,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partner_settlement_periods';

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
        'period_start'    => 'date',
        'period_end'      => 'date',
        'issue_date'      => 'date',
        'total_gmv'       => 'float',
        'total_commission'=> 'float',
        'commission_rate' => 'float',
        'issued_at'       => 'datetime',
        'paid_at'         => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Get the partner for this settlement period.
     *
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Get the admin who confirmed the payment.
     *
     * @return BelongsTo
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get the line items for this settlement period.
     *
     * @return HasMany
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(SettlementLineItem::class, 'settlement_period_id');
    }

    /**
     * Get the adjustments for this settlement period.
     *
     * @return HasMany
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(SettlementAdjustment::class, 'settlement_period_id');
    }

    /**
     * Tổng điều chỉnh.
     *
     * @return float
     */
    public function getTotalAdjustmentsAttribute(): float
    {
        return (float) $this->adjustments->sum('amount');
    }

    /**
     * Thực thu hoa hồng cuối cùng cần Partner thanh toán (Net Commission to Pay).
     *
     * @return float
     */
    public function getNetCommissionToPayAttribute(): float
    {
        return round($this->total_commission + $this->total_adjustments, 2);
    }
}
