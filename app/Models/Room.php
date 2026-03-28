<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get the building that owns this room.
     *
     * @return BelongsTo
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
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
}
