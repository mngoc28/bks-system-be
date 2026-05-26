<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PartnerInfo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "partner_info";

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
        "approved_at" => "datetime",
    ];

    /**
     * Get the user (partner) that owns this partner info.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * Get the province for this partner info.
     *
     * @return BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, "province_id");
    }

    /**
     * Get the ward for this partner info.
     *
     * @return BelongsTo
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, "ward_id");
    }

    /**
     * Get the user who created this partner info.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated this partner info.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * Get the user who approved this partner info.
     *
     * @return BelongsTo
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "approved_by");
    }
}
