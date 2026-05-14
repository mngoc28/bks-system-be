<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'number_of_floors'  => 'integer',
        'number_of_units'   => 'integer',
        'year_built'        => 'integer',
        'property_type_id'  => 'integer',
        'rent_category'     => 'integer',
        'area'              => 'decimal:2',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'property_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'property_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
