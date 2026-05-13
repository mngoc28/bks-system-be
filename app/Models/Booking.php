<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Booking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookings';

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
        'status'      => 'integer',
        'stay_status' => 'string',
        'start_date'  => 'date',
        'end_date'    => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Get the user who made this booking.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the room that is booked.
     *
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the room price for this booking.
     *
     * @return BelongsTo
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(RoomPrice::class, 'price_id');
    }

    /**
     * Get the user who created this booking.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this booking.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the services for this booking.
     *
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services', 'booking_id', 'service_id')
            ->withTimestamps();
    }
    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contract::class, 'booking_id');
    }

    /**
     * Append-only timeline of lifecycle events for this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timelineEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BookingTimelineEvent::class, 'booking_id');
    }

    /**
     * Summary of getStartDateAttribute
     * @param mixed $value
     * @return string
     */
    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d');
    }

    /**
     * Summary of getEndDateAttribute
     * @param mixed $value
     * @return string|null
     */
    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d') : null;
    }

    /**
     * Summary of getCreatedAtAttribute
     * @param mixed $value
     * @return string
     */

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
    }

    /**
     * Summary of getUpdatedAtAttribute
     * @param mixed $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
    }
}
