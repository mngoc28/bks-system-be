<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CancellationReasonCode extends Model
{
    protected $table = 'cancellation_reason_codes';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requires_note' => 'boolean',
        'sort_order'    => 'integer',
        'is_active'     => 'boolean',
    ];
}
