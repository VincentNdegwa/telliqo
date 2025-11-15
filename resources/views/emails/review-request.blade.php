@if ($isReminder)
<x-mail::message>
Hi {{ $reviewRequest->customer->name }},

{{ $reviewRequest->message }}

<x-mail::button :url="route('review-request.show', $reviewRequest->unique_token)">
Leave Your Review
</x-mail::button>

---

**This review request expires soon**, so please take a moment to share your thoughts.

@if ($reviewRequest->business->email)
Questions? Contact us at [{{ $reviewRequest->business->email }}](mailto:{{ $reviewRequest->business->email }})
@endif

Don't want to receive review requests? [Unsubscribe]({{ route('review-request.opt-out', $reviewRequest->unique_token) }})

Thanks,<br>
{{ $reviewRequest->business->name }}
</x-mail::message>
@else


<x-mail::message>
Hi {{ $reviewRequest->customer->name }},

{{ $reviewRequest->message }}

<x-mail::button :url="route('review-request.show', $reviewRequest->unique_token)">
Leave Your Review
</x-mail::button>

---

Your feedback helps us improve and serve you better. It only takes a minute!

@if ($reviewRequest->business->email)
Questions? Contact us at [{{ $reviewRequest->business->email }}](mailto:{{ $reviewRequest->business->email }})
@endif

Don't want to receive review requests? [Unsubscribe]({{ route('review-request.opt-out', $reviewRequest->unique_token) }})

Thanks,<br>
{{ $reviewRequest->business->name }}
</x-mail::message>
@endif
