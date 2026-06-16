<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Message extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at'  => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the metadata attribute, resolving relative image paths.
     *
     * @param string|array|null $value
     * @return array|null
     */
    public function getMetadataAttribute($value): ?array
    {
        if (!$value) {
            return null;
        }

        $metadata = is_string($value) ? json_decode($value, true) : $value;

        if (is_array($metadata) && isset($metadata['type']) && $metadata['type'] === 'image' && isset($metadata['url'])) {
            $url = (string) $metadata['url'];
            if (!str_starts_with($url, 'http') && !str_starts_with($url, 'data:') && !str_starts_with($url, 'blob:')) {
                $baseUrl = rtrim((string) config('const.CLOUDINARY_HEADER_IMAGE_URL'), '/');
                $path = '/' . ltrim($url, '/');
                $metadata['url'] = $baseUrl . $path;
            }
        }

        return $metadata;
    }
}
