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
            font-size: 80px; 
            font-weight: bold; 
            color: white; 
            margin-bottom: 40px;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .subtitle { 
            font-size: 44px; 
            color: white; 
            margin-bottom: 60px;
            text-align: center;
            max-width: 80%;
        }
        .qr-container { 
            background: white; 
            padding: 50px; 
            border-radius: 40px; 
            transform: rotate(-2deg);
            box-shadow: 0 25px 70px rgba(0,0,0,0.3);
        }
        .qr-container img {
            display: block;
            width: {{ $qrSize }}px;
            height: {{ $qrSize }}px;
        }
        .footer { 
            margin-top: 50px; 
            font-size: 32px; 
            color: white;
            font-weight: bold;
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
        <div class="footer">📱 Scan Me!</div>
    </div>
</body>
</html>
