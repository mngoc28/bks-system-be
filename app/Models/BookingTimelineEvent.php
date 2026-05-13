<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit log for booking lifecycle events.
 *
 * Inserted by BookingTimelineService whenever a booking transitions
 * (created, confirmed, cancelled, checked_in, checked_out, no_show, conflict_detected).
 */
final class BookingTimelineEvent extends Model
{
    use HasFactory;

    protected $table = 'booking_timeline_events';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
