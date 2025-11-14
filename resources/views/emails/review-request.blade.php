<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reviewRequest->subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            max-width: 150px;
            height: auto;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            margin-bottom: 10px;
        }
        .message {
            font-size: 16px;
            margin: 20px 0;
            white-space: pre-wrap;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2563eb;
            color: #fff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 30px 0;
        }
        .cta-button:hover {
            background-color: #1d4ed8;
        }
        .button-container {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .opt-out {
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
        .opt-out a {
            color: #9ca3af;
            text-decoration: underline;
        }
        .reminder-badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        @if ($isReminder)
            <div style="text-align: center;">
                <span class="reminder-badge">REMINDER</span>
            </div>
        @endif

        @if ($reviewRequest->business->logo)
            <div class="logo">
                <img src="{{ asset('storage/' . $reviewRequest->business->logo) }}" alt="{{ $reviewRequest->business->name }}">
            </div>
        @else
            <div class="business-name">
                {{ $reviewRequest->business->name }}
            </div>
        @endif

        <p>Hi {{ $reviewRequest->customer->name }},</p>

        <div class="message">{{ $reviewRequest->message }}</div>

        <div class="button-container">
            <a href="{{ route('review-request.show', $reviewRequest->unique_token) }}" class="cta-button">
                Leave Your Review
            </a>
        </div>

        <div class="footer">
            <p>This review request was sent by {{ $reviewRequest->business->name }}</p>
            
            @if ($reviewRequest->business->email)
                <p>Questions? Contact us at <a href="mailto:{{ $reviewRequest->business->email }}">{{ $reviewRequest->business->email }}</a></p>
            @endif

            <div class="opt-out">
                <p>Don't want to receive review requests? <a href="{{ route('review-request.opt-out', $reviewRequest->unique_token) }}">Unsubscribe</a></p>
            </div>
        </div>
    </div>
</body>
</html>
