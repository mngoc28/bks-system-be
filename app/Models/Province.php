<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

final class Province extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "provinces";

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
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the wards for this province.
     *
     * @return HasMany
     */
    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class, "province_id");
    }

    /**
     * Get the buildings for this province.
     *
     * @return HasMany
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, "province_id");
    }

    /**
     * Get the user who created this province.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated this province.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }
}
