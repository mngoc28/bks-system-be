<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RoomTouristSpotMap;

final class TouristSpot extends Model
{
    use HasFactory;

    protected $table = 'tourist_spots';

    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function roomTouristSpotMaps(): HasMany
    {
        return $this->hasMany(RoomTouristSpotMap::class, 'tourist_spot_id');
    }
}
