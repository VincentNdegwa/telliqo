<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ReviewRequest extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'unique_token',
        'status',
        'subject',
        'message',
        'sent_at',
        'opened_at',
        'completed_at',
        'expires_at',
        'reminder_sent_count',
        'last_reminder_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_reminder_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ReviewRequest $request) {
            if (empty($request->unique_token)) {
                $request->unique_token = Str::random(64);
            }
            if (empty($request->expires_at)) {
                $request->expires_at = now()->addDays(30);
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOpened($query)
    {
        return $query->where('status', 'opened');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function markAsOpened(): void
    {
        if ($this->status === 'pending') {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canSendReminder(): bool
    {
        return $this->status === 'pending' 
            && !$this->isExpired()
            && ($this->last_reminder_at === null || $this->last_reminder_at->diffInDays(now()) >= 3);
    }
}
