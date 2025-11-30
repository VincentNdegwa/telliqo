<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'body',
        'parent_id',
        'mentions_meta',
    ];

    protected $casts = [
        'mentions_meta' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function getRenderedBodyAttribute(): string
    {
        $body = $this->body ?? '';

        if (! is_array($this->mentions_meta) || empty($this->mentions_meta)) {
            return $body;
        }

        $meta = $this->mentions_meta;

        usort($meta, function ($a, $b) {
            $aIndex = isset($a['index']) ? (int) $a['index'] : 0;
            $bIndex = isset($b['index']) ? (int) $b['index'] : 0;

            return $aIndex <=> $bIndex;
        });

        foreach ($meta as $item) {
            if (! isset($item['index'], $item['name'])) {
                continue;
            }

            $placeholder = '{{' . $item['index'] . '}}';
            $body = str_replace($placeholder, '@' . $item['name'], $body);
        }

        return $body;
    }
}
