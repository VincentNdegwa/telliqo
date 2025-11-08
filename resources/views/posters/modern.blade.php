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
            background-color: {{ $bgColor }}; 
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
            font-size: 72px; 
            font-weight: bold; 
            color: {{ $primaryColor }}; 
            margin-bottom: 40px;
            text-align: center;
        }
        .subtitle { 
            font-size: 42px; 
            color: #4b5563; 
            margin-bottom: 60px;
            text-align: center;
            max-width: 80%;
            line-height: 1.4;
        }
        .qr-container { 
            background: white; 
            padding: 40px; 
            border-radius: 30px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .qr-container img {
            display: block;
            width: {{ $qrSize }}px;
            height: {{ $qrSize }}px;
        }
        .footer { 
            margin-top: 50px; 
            font-size: 28px; 
            color: #6b7280;
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
        <div class="footer">Scan with your phone's camera</div>
    </div>
</body>
</html>
