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
        body { 
            margin: 0; 
            padding: 0; 
            font-family: 'Arial', sans-serif; 
            background: {{ $bgColor }};
            width: 100%;
            height: 100vh;
        }
        .container { 
            width: 100%; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            padding: 60px;
        }
        .title { 
            font-size: 68px; 
            font-weight: 300; 
            color: #d4af37; 
            margin-bottom: 35px;
            text-align: center;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .subtitle { 
            font-size: 38px; 
            color: #e5e5e5; 
            margin-bottom: 70px;
            text-align: center;
            font-style: italic;
            font-weight: 300;
        }
        .qr-container { 
            background: white; 
            padding: 40px; 
            border: 4px solid #d4af37;
        }
        .qr-container img {
            display: block;
            width: {{ $qrSize }}px;
            height: {{ $qrSize }}px;
        }
        .footer { 
            margin-top: 50px; 
            font-size: 26px; 
            color: #d4af37;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">{{ $businessName }}</div>
        <div class="subtitle">{{ $customText }}</div>
        <div class="qr-container">
            {!! $qrSvg !!}
        </div>
        <div class="footer">SCAN TO REVIEW</div>
    </div>
</body>
</html>
