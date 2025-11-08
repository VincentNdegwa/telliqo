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
            font-size: 64px; 
            font-weight: 300; 
            color: {{ $primaryColor }}; 
            margin-bottom: 30px;
            text-align: center;
        }
        .subtitle { 
            font-size: 36px; 
            color: #6b7280; 
            margin-bottom: 70px;
            text-align: center;
            font-weight: 300;
        }
        .qr-container { 
            border: 2px solid {{ $primaryColor }};
            padding: 30px;
        }
        .qr-container img {
            display: block;
            width: {{ $qrSize }}px;
            height: {{ $qrSize }}px;
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
    </div>
</body>
</html>
