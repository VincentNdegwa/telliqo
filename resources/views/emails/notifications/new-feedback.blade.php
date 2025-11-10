<x-mail::message>
# New Feedback Received

You've received new feedback for **{{ $business->name }}**.

## Feedback Details

**Customer:** {{ $feedback->customer_name ?? 'Anonymous' }}  
**Email:** {{ $feedback->customer_email ?? 'Not provided' }}  
**Rating:** {!! str_repeat('⭐', $feedback->rating) !!} ({{ $feedback->rating }}/5)

@if($feedback->comment)
**Comment:**
> {{ $feedback->comment }}
@endif

**Submitted:** {{ $feedback->created_at->format('M d, Y \a\t h:i A') }}

<x-mail::button :url="config('app.url') . '/dashboard'">
View in Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
