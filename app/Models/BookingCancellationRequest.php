<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BookingCancellationRequest extends Model
{
    protected $table = 'booking_cancellation_requests';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'previous_booking_status' => 'integer',
        'requested_at'            => 'datetime',
        'resolved_at'             => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
