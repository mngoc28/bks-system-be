<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class Room extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rooms';

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
        'deposit'      => 'decimal:2',
        'area'         => 'decimal:2',
        'floor_number' => 'integer',
        'people'       => 'integer',
        'room_type'    => 'integer',
        'status'       => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get the images for this room.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class, 'room_id');
    }

    /**
     * Get the amenities associated with this room.
     *
     * @return BelongsToMany
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id')
            ->withTimestamps();
    }

    /**
     * Get the services associated with this room.
     *
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'room_services', 'room_id', 'service_id')
            ->withTimestamps();
    }

    /**
     * Get the prices for this room.
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(RoomPrice::class, 'room_id');
    }

    /**
     * Get the bookings for this room.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'room_id');
    }

    /**
     * Get the utility fees for this room.
     *
     * @return HasMany
     */
    public function utilityFees(): HasMany
    {
        return $this->hasMany(UtilityFee::class, 'room_id');
    }

    /**
     * Tourist spot mappings for this room.
     *
     * @return HasMany
     */
    public function touristSpotMaps(): HasMany
    {
        return $this->hasMany(RoomTouristSpotMap::class, 'room_id');
    }

    /**
     * Get the user who created this room.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this room.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get reviews for this room.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'room_id');
    }

    /**
     * Scope to join base tables required for room cards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithBaseJoins($query)
    {
        return $query
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->leftJoin('partner_info as pi', 'pi.user_id', '=', 'u.id')
            ->join('provinces as p', 'b.province_id', '=', 'p.id')
            ->leftJoin('property_types as pt', 'b.property_type_id', '=', 'pt.id')
            ->leftJoin('room_prices as rp', 'rooms.id', '=', 'rp.room_id')
            ->leftJoin('room_images as ri', function ($join) {
                $join->on('rooms.id', '=', 'ri.room_id')
                    ->where('ri.sort', 1);
            });
    }

    /**
     * Get SQL expression for cheapest daily price.
     *
     * @return string
     */
    public static function cheapestDailyPriceSql(): string
    {
        return 'ROUND(CASE
            WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                AND MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) IS NOT NULL
            THEN (CASE WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                    < MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) END)
            WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
            THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
            ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
        END, 0)';
    }

    /**
     * Get database-specific SQL expression for aggregated room prices.
     *
     * @return string
     */
    public static function allPricesSql(): string
    {
        if (DB::getDriverName() === 'sqlite') {
            return <<<'SQL'
COALESCE(
    '[' || GROUP_CONCAT(DISTINCT
        '{"unit":"' || rp.unit || '", "price":' || rp.price ||
        ', "deposit_amount":' || COALESCE(rp.deposit_amount, 0) ||
        ', "minimum_stay":' || COALESCE(rp.minimum_stay, 0) || '}'
    ) || ']',
    '[]'
)
SQL;
        }

        return 'IFNULL(CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
            '\'{"unit":"\', rp.unit, \'", "price":\', rp.price, ' .
            '\', "deposit_amount":\', IFNULL(rp.deposit_amount, 0), ' .
            '\', "minimum_stay":\', IFNULL(rp.minimum_stay, 0), \'}\'' .
            ')), \']\'), \'[]\')';
    }
}
