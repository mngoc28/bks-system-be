<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bản ghi chặn lịch phòng (Partner Portal 360 Phase 3).
 *
 * `block_type` chỉ nhận một trong ba giá trị: `maintenance`, `owner_use`,
 * `off_market`. Ràng buộc thực thi ở DB qua CHECK constraint
 * (`chk_rb_block_type`) và `RoomBlockService` thông qua FormRequest.
 */
final class RoomBlock extends Model
{
    use HasFactory;

    public const BLOCK_TYPE_MAINTENANCE = 'maintenance';
    public const BLOCK_TYPE_OWNER_USE   = 'owner_use';
    public const BLOCK_TYPE_OFF_MARKET  = 'off_market';

    public const BLOCK_TYPES = [
        self::BLOCK_TYPE_MAINTENANCE,
        self::BLOCK_TYPE_OWNER_USE,
        self::BLOCK_TYPE_OFF_MARKET,
    ];

    protected $table = 'room_blocks';

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
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
