<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotAnswer extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'chatbot_answers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'question_id',
        'content',
        'next_question_id',
        'is_final',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_final' => 'bool',
    ];
}
