<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RoomTouristSpotMap extends Model
{
    use HasFactory;

    protected $table = 'room_tourist_spot_maps';

    protected $guarded = [];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'travel_time_minutes' => 'integer',
        'priority_order' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function touristSpot(): BelongsTo
    {
        return $this->belongsTo(TouristSpot::class, 'tourist_spot_id');
    }
}
