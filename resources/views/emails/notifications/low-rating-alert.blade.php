<x-mail::message>
# ⚠️ Low Rating Alert

You've received a low rating ({{ $feedback->rating }}/5) for **{{ $business->name }}**.

This rating is at or below your alert threshold of {{ $threshold }} stars. Immediate attention may be required.

## Feedback Details

**Customer:** {{ $feedback->name ?? 'Anonymous' }}  
**Email:** {{ $feedback->email ?? 'Not provided' }}  
**Rating:** {!! str_repeat('⭐', $feedback->rating) !!} {{ str_repeat('☆', 5 - $feedback->rating) }} ({{ $feedback->rating }}/5)

@if($feedback->comment)
**Comment:**
> {{ $feedback->comment }}
@endif

**Submitted:** {{ $feedback->created_at->format('M d, Y \a\t h:i A') }}

## Recommended Actions

- Review the feedback and identify areas for improvement
- Respond promptly to show you value customer feedback
- Consider reaching out directly if contact information was provided

<x-mail::button :url="config('app.url') . '/dashboard'">
View & Respond
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
