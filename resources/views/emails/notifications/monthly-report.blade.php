<x-mail::message>
# Monthly Feedback Report

Here's your comprehensive monthly report for **{{ $business->name }}**.

**Period:** {{ $reportData['month_name'] }} {{ $reportData['year'] }}

## Overview

<x-mail::panel>
**Total Feedback:** {{ $reportData['total_count'] }}  
**Average Rating:** {!! str_repeat('⭐', round($reportData['average_rating'])) !!} {{ number_format($reportData['average_rating'], 1) }}/5  
**Response Rate:** {{ $reportData['response_rate'] }}%  
**Growth:** {{ $reportData['growth_percentage'] > 0 ? '+' : '' }}{{ $reportData['growth_percentage'] }}% vs last month
</x-mail::panel>

## Monthly Trends

@if($reportData['total_count'] > 0)
- **Best Day:** {{ $reportData['best_day'] }} ({{ $reportData['best_day_count'] }} feedbacks)
@if($reportData['peak_hour'] !== 'N/A')
- **Most Active Hour:** {{ $reportData['peak_hour'] }}:00 - {{ $reportData['peak_hour'] + 1 }}:00
@else
- **Most Active Hour:** Not available
@endif
@else
- No feedback received this month
@endif

## Rating Distribution

@foreach($reportData['rating_breakdown'] as $rating => $count)
- {{ $rating }} stars: {{ $count }} ({{ $reportData['total_count'] > 0 ? round(($count / $reportData['total_count']) * 100) : 0 }}%)
@endforeach

@if(isset($reportData['sentiment_breakdown']))
## Sentiment Analysis

@foreach($reportData['sentiment_breakdown'] as $sentiment => $data)
- {{ ucfirst($sentiment) }}: {{ $data['count'] }} ({{ $data['percentage'] }}%)
@endforeach
@endif

@if(isset($reportData['top_keywords']) && count($reportData['top_keywords']) > 0)
## Trending Keywords

{{ implode(', ', $reportData['top_keywords']) }}
@endif

@if(isset($reportData['improvement_areas']) && count($reportData['improvement_areas']) > 0)
## Areas for Improvement

@foreach($reportData['improvement_areas'] as $area)
- {{ $area }}
@endforeach
@endif

<x-mail::button :url="config('app.url') . '/dashboard'">
View Detailed Analytics
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
