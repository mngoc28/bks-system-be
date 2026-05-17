<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CancellationPolicyTier extends Model
{
    protected $table = 'cancellation_policy_tiers';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'hours_before_checkin_min' => 'integer',
        'hours_before_checkin_max' => 'integer',
        'fee_percent'              => 'decimal:2',
        'refund_percent'           => 'decimal:2',
    ];
}
