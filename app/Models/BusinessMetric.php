<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessMetric extends Model
{
    protected $fillable = [
        'business_id',
        'metric_date',
        'total_feedback',
        'avg_rating',
        'promoters',
        'passives',
        'detractors',
        'nps',
        'positive_count',
        'neutral_count',
        'negative_count',
        'not_determined_count',
        'top_keywords',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'avg_rating' => 'decimal:2',
            'top_keywords' => 'array',
            'meta' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('metric_date', [$start, $end]);
    }

    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('metric_date', '>=', now()->subDays($days)->format('Y-m-d'));
    }
}
