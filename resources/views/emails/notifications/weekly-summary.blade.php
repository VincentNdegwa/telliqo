<x-mail::message>
# Weekly Feedback Summary

Here's your weekly feedback summary for **{{ $business->name }}**.

**Period:** {{ $summaryData['start_date'] }} - {{ $summaryData['end_date'] }}

## Key Metrics

<x-mail::panel>
**Total Feedback:** {{ $summaryData['total_count'] }}  
**Average Rating:** {!! str_repeat('⭐', round($summaryData['average_rating'])) !!} {{ number_format($summaryData['average_rating'], 1) }}/5  
**Response Rate:** {{ $summaryData['response_rate'] }}%
</x-mail::panel>

## Rating Breakdown

@foreach($summaryData['rating_breakdown'] as $rating => $count)
- {{ $rating }} stars: {{ $count }} ({{ $summaryData['total_count'] > 0 ? round(($count / $summaryData['total_count']) * 100) : 0 }}%)
@endforeach

@if(isset($summaryData['sentiment_breakdown']))
## Sentiment Analysis

@foreach($summaryData['sentiment_breakdown'] as $sentiment => $count)
- {{ ucfirst($sentiment) }}: {{ $count }}
@endforeach
@endif

@if(isset($summaryData['top_keywords']) && count($summaryData['top_keywords']) > 0)
## Top Keywords

{{ implode(', ', $summaryData['top_keywords']) }}
@endif

<x-mail::button :url="config('app.url') . '/dashboard'">
View Full Report
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
