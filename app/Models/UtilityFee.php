<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UtilityFee extends Model
{
    use HasFactory;

    protected $table = 'utility_fees';

    protected $fillable = [
        'room_id',
        'fee_type',
        'calc_method',
        'unit_price',
        'is_included',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'is_included' => 'boolean',
    ];

    /**
     * Get the room that this utility fee belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
