<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $room_id
 * @property int $price_id
 * @property int $status
 * @property string $stay_status
 * @property string $payment_status
 * @property Carbon|string $start_date
 * @property Carbon|string $end_date
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property string|null $customer_email
 * @property string|null $cancellation_reason
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['total_amount', 'amount_paid', 'amount_remaining'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status'                      => 'integer',
        'stay_status'                 => 'string',
        'payment_status'              => 'string',
        'start_date'                  => 'date',
        'end_date'                    => 'date',
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
        'pending_cancellation_since'  => 'datetime',
        'payment_method_changed_at'   => 'datetime',
        'cancellation_policy_version' => 'string',
        'client_local_id'             => 'string',
        'client_fingerprint'          => 'string',
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
     * Get the booking deposit associated with this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bookingDeposit(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BookingDeposit::class, 'booking_id');
    }

    /**
     * Append-only timeline of lifecycle events for this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(BookingTimelineEvent::class, 'booking_id');
    }

    /**
     * Guest-initiated cancellation requests (BCP).
     *
     * @return HasMany<BookingCancellationRequest>
     */
    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(BookingCancellationRequest::class, 'booking_id');
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

    /**
     * Get reviews for this booking.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'booking_id');
    }

    /**
     * Get the settlement period that contains this booking.
     *
     * @return BelongsTo
     */
    public function settlementPeriod(): BelongsTo
    {
        return $this->belongsTo(PartnerSettlementPeriod::class, 'settlement_period_id');
    }

    /**
     * Get the settlement line item corresponding to this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settlementLineItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SettlementLineItem::class, 'booking_id');
    }

    /**
     * Tổng tạm tính (tiền phòng theo unit gói giá + dịch vụ).
     */
    public function getTotalAmountAttribute(): float
    {
        return \App\Services\BookingStayAmountCalculator::computeGrandTotalForBooking($this);
    }

    public function getAmountPaidAttribute(): float
    {
        return \App\Services\BookingPaymentStatusService::getAmountPaid($this);
    }

    public function getAmountRemainingAttribute(): float
    {
        return \App\Services\BookingPaymentStatusService::getAmountRemaining($this);
    }
}
