<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'booking_id',
        'type',
        'title',
        'description',
        'severity',
        'status',
        'admin_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reporter_id' => 'integer',
        'reported_user_id' => 'integer',
        'booking_id' => 'integer',
    ];
}
