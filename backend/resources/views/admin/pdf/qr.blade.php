<!DOCTYPE html>
<html>

<head>
    <title>QR Code - {{ $name }}</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
        }

        .container {
            margin-top: 50px;
        }

        .qr-code {
            max-width: 500px;
            margin: 0 auto;
        }

        .name {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .url {
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="name">Bio Page: {{ $name }}</div>
        <div class="qr-code">
            <img src="data:image/svg+xml;base64,{{ $qrImage }}" alt="QR Code" width="500">
        </div>
        <div class="url">
            <p>Scan to visit:</p>
            <a href="{{ $url }}">{{ $url }}</a>
        </div>
    </div>
</body>

</html>