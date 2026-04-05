<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Building extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "buildings";

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
        "number_of_floors"  => "integer",
        "number_of_units"   => "integer",
        "year_built"        => "integer",
        "property_type_id"  => "integer",
        "rent_category"     => "integer",
        "area"              => "decimal:2",
        "created_at"        => "datetime",
        "updated_at"        => "datetime",
    ];

    /**
     * Get the user for this building.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * Get the property type for this building.
     *
     * @return BelongsTo
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, "property_type_id");
    }

    /**
     * Get the province for this building.
     *
     * @return BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, "province_id");
    }

    /**
     * Get the ward for this building.
     *
     * @return BelongsTo
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, "ward_id");
    }

    /**
     * Get the rooms for the building.
     *
     * @return HasMany
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, "building_id");
    }

    /**
     * Get the building images for this building.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(BuildingImage::class, "building_id");
    }

    /**
     * Get the user who created this building.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated this building.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }
}
