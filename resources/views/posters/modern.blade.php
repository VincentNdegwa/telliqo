<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }
        body { 
            font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
            background-color: {{ $bgColor }};
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .poster {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 40px 30px;
            position: relative;
        }
        .header {
            text-align: center;
            flex-shrink: 0;
        }
        .logo { 
            margin-bottom: 20px;
            max-width: 120px;
            max-height: 80px;
            display: block;
        }
        .logo img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            object-fit: contain;
        }
        .title { 
            font-size: 48px;
            font-weight: 700;
            color: {{ $textColor }};
            margin: 0;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            gap: 25px;
            min-height: 0;
        }
        .subtitle { 
            font-size: 20px;
            color: {{ $textColor }};
            opacity: 0.75;
            text-align: center;
            max-width: 90%;
            line-height: 1.4;
            font-weight: 500;
        }
        .qr-wrapper {
            padding: 25px;
            border-radius: 16px;
            position: relative;
            width: calc({{ $qrSize }}px + 50px);
            height: calc({{ $qrSize }}px + 50px);
            text-align: center;
            line-height: calc({{ $qrSize }}px + 50px);
        }
        .qr-wrapper svg {
            width: {{ $qrSize }}px !important;
            height: {{ $qrSize }}px !important;
            vertical-align: middle;
            display: inline-block;
        }
        .footer {
            text-align: center;
            flex-shrink: 0;
        }
        .footer-text { 
            font-size: 16px;
            color: {{ $textColor }};
            opacity: 0.6;
            margin: 0;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>
    <div class="poster">
        <div class="header">
            @if($logoUrl)
            <div class="logo">
                <img src="{{ $logoUrl }}" alt="{{ $businessName }} Logo">
            </div>
            @endif
            <h1 class="title">{{ $businessName }}</h1>
        </div>
        
        <div class="content">
            <p class="subtitle">{{ $customText }}</p>
            <div class="qr-wrapper">
                {!! $qrSvg !!}
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text"> Scan to Share Your Feedback</p>
        </div>
    </div>
</body>
</html>
