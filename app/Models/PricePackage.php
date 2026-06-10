<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PricePackage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'price_packages';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the room prices for this price package.
     *
     * @return HasMany
     */
    public function roomPrices(): HasMany
    {
        return $this->hasMany(RoomPrice::class, 'price_package_id');
    }

    /**
     * Get the user who created this price package.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this price package.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Default package id (medium → first row fallback).
     */
    public static function defaultId(): int
    {
        $mediumId = self::query()->where('name', 'medium')->value('id');

        return (int) ($mediumId ?? self::query()->orderBy('id')->value('id') ?? 0);
    }

    /**
     * Resolve price package id from explicit id or legacy label.
     */
    public static function resolveId(?int $packageId, ?string $label = null): int
    {
        if ($packageId !== null && $packageId > 0 && self::query()->where('id', $packageId)->exists()) {
            return $packageId;
        }

        if ($label !== null && trim($label) !== '') {
            $trimmed = trim($label);
            $byName = self::query()->where('name', $trimmed)->value('id');
            if ($byName) {
                return (int) $byName;
            }

            $aliases = [
                'gói chuẩn' => 'medium',
                'goi chuan' => 'medium',
                'chuẩn' => 'medium',
                'chuan' => 'medium',
                'siêu nhỏ' => 'super small',
                'sieu nho' => 'super small',
                'nhỏ' => 'small',
                'nho' => 'small',
                'trung bình' => 'medium',
                'trung binh' => 'medium',
                'lớn' => 'large',
                'lon' => 'large',
            ];
            $key = mb_strtolower($trimmed);
            if (isset($aliases[$key])) {
                $aliasId = self::query()->where('name', $aliases[$key])->value('id');
                if ($aliasId) {
                    return (int) $aliasId;
                }
            }
        }

        return self::defaultId();
    }
}
