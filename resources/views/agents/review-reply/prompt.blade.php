Generate a professional reply to this {{ $review['sentiment'] }} review from {{ $review['customer_name'] }}.

REVIEW DETAILS:
- Rating: {{ $review['rating'] }}/5 stars
- Customer: {{ $review['customer_name'] }}
- Comment: "{{ $review['comment'] }}"
- Sentiment: {{ $review['sentiment'] }}

BUSINESS CONTEXT:
- You are replying on behalf of {{ $business['name'] }}
- Business Type: {{ $business['category'] }}
@if($business['description'])
- About: {{ $business['description'] }}
@endif

TASK:
Write a {{ $review['sentiment'] }} reply that:
- Thanks {{ $review['customer_name'] }} by name
- Addresses their specific feedback: "{{ \Illuminate\Support\Str::limit($review['comment'], 80) }}"
- Reflects {{ $business['name'] }}'s brand as a {{ $business['category'] }}
- Is warm, genuine, and concise (2-4 sentences maximum)
@if($review['sentiment'] === 'negative')
- Includes a sincere apology and offers resolution
@elseif($review['sentiment'] === 'positive')
- Expresses genuine gratitude and invites them back
@else
- Acknowledges their feedback and shows willingness to improve
@endif

Generate ONLY the reply text. No explanations, no metadata, no quotes around the reply.
