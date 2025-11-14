<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'email',
        'phone',
        'company_name',
        'tags',
        'notes',
        'total_requests_sent',
        'total_feedbacks',
        'last_request_sent_at',
        'last_feedback_at',
        'opted_out',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'last_request_sent_at' => 'datetime',
            'last_feedback_at' => 'datetime',
            'opted_out' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('opted_out', false);
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
