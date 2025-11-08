<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            min-height: 297mm;
            display: table;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .poster {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 10px;
        }

        .content-wrapper {
            display: inline-block;
            max-width: 700px;
            page-break-inside: avoid;
        }

        .logo-title-row {
            display: table;
            margin: 0 auto 30px;
        }

        .logo-cell {
            display: table-cell;
            vertical-align: middle;
            padding-right: 20px;
        }

        #logo-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
        }

        #logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .title-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }

        .title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -1px;
            margin: 0;
        }

        .description {
            font-size: 16px;
            opacity: 0.85;
            line-height: 1.5;
            margin: 0 0 20px 0;
            text-align: center;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .qr-section {
            text-align: center;
            page-break-inside: avoid;
        }

        .qr-wrapper {
            background: white;
            padding: 25px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 25px;
        }

        .qr-wrapper img {
            display: block;
        }

        .footer {
            font-size: 16px;
            opacity: 0.7;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-align: center;
        }
    </style>
</head>
<body style="
    @if($bgImage)
        background-image: url('{{ $bgImage }}');
        background-size: cover;
        background-position: center;
    @else
        background-color: {{ $bgColor }};
    @endif
">
    <div class="container" style="
        @if($bgImage)
            background-color: {{ $bgColor }}cc;
        @endif
    ">
        <div class="poster">
            <div class="content-wrapper">
                @if (($showLogo && $logoData) || ($showTitle && $title))
                    <div class="logo-title-row">
                        @if ($showLogo && $logoData)
                            <div class="logo-cell">
                                <div id="logo-container">
                                    <img id="logo" src="{{ $logoData }}" alt="Logo">
                                </div>
                            </div>
                        @endif

                        @if ($showTitle && $title)
                            <div class="title-cell">
                                <h1 class="title" style="color: {{ $textColor }};">{{ $title }}</h1>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($showDescription && $description)
                    <div class="description" style="color: {{ $textColor }};">{!! $description !!}</div>
                @endif

                <div class="qr-section">
                    <div class="qr-wrapper">
                        <img src="{{ $qrSvg }}" alt="QR Code" style="width: {{ $qrSize }}px; height: {{ $qrSize }}px;">
                    </div>

                    @if ($showFooter && $footer)
                        <p class="footer" style="color: {{ $textColor }};">{{ $footer }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
