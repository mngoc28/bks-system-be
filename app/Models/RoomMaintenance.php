<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phiếu bảo trì / sự cố phòng (Partner maintenance module).
 */
final class RoomMaintenance extends Model
{
    use HasFactory;

    public const TYPE_SCHEDULED = 'scheduled';

    public const TYPE_EMERGENCY = 'emergency';

    public const STATUS_PLANNED = 'planned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const SOURCE_PARTNER = 'partner';

    public const SOURCE_GUEST_REPORT = 'guest_report';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'room_id',
        'property_id',
        'title',
        'description',
        'images',
        'maintenance_type',
        'start_time',
        'end_time',
        'status',
        'room_block_id',
        'block_calendar',
        'source',
        'cancellation_reason',
        'started_at',
        'completed_at',
        'cancelled_at',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_time'      => 'datetime',
        'end_time'        => 'datetime',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
        'images'          => 'array',
        'block_calendar'  => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function roomBlock(): BelongsTo
    {
        return $this->belongsTo(RoomBlock::class, 'room_block_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
