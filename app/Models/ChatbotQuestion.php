<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotQuestion extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'chatbot_questions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'content',
        'type',
        'position_x',
        'position_y',
        'created_by',
        'updated_by',
        'is_start_node',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [];

    /**
     * Chatbot answers relationship.
     */
    public function answers()
    {
        return $this->hasMany(ChatbotAnswer::class, 'question_id');
    }
}
