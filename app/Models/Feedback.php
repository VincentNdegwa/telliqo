<?php

namespace App\Models;

use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory, Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'customer_id',
        'review_request_id',
        'customer_name',
        'customer_email',
        'rating',
        'comment',
        'sentiment',
        'moderation_status',
        'is_public',
        'replied_at',
        'reply_text',
        'submitted_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'submitted_at' => 'datetime',
            'replied_at' => 'datetime',
            'moderation_status' => ModerationStatus::class,
            'sentiment' => Sentiments::class,
        ];
    }

    /**
     * Get the business that owns the feedback.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reviewRequest(): BelongsTo
    {
        return $this->belongsTo(ReviewRequest::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)
            ->where('moderation_status', 'approved');
    }

    /**
     * Scope a query to only include approved feedback.
     */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Scope a query to only include pending feedback.
     */
    public function scopePending($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    /**
     * Check if feedback has been replied to.
     */
    public function hasReply(): bool
    {
        return ! is_null($this->replied_at) && ! is_null($this->reply_text);
    }
}
