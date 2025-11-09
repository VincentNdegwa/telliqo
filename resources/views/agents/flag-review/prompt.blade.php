Review: "{{ $reviewText }}"
@if(isset($rating))
Rating: {{ $rating }}/5 stars
@endif
@if(isset($concerns) && !empty($concerns))
Automated Detection: {{ implode(', ', $concerns) }}
@endif

Analyze this review for SPAM and ABUSE only. Remember:
- Negative feedback and complaints are ALLOWED
- Only flag: spam URLs/emails/phones, hate speech, severe profanity (F/C-word), threats, explicit content
- Mild language ("hate", "worst", "awful", "stupid") is NORMAL customer feedback
- When uncertain, default to safe (should_flag=false, confidence=0.0)

Respond with JSON only: {"should_flag": boolean, "confidence": 0.0-1.0, "reason": "brief explanation"}
