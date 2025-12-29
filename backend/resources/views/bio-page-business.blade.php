<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta title="{{ $name }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:
                {{ $color ?? '#0077b5' }}
            ;
            --bg:
                {{ $bg_color ?? '#f3f2ef' }}
            ;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--bg);
            margin: 0;
            color: #333;
        }

        .cover {
            height: 180px;
            background-color: #a0a0a0;
            background-size: cover;
            background-position: center;
        }

        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            padding: 0 20px 40px;
        }

        .header-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: -50px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
            position: relative;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            border: 4px solid white;
            position: absolute;
            top: -50px;
            left: 20px;
            background: white;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .info {
            margin-top: 50px;
        }

        h1 {
            margin: 0;
            font-size: 1.6rem;
        }

        .website {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .grid-links {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .grid-link {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: border-color 0.2s;
        }

        .grid-link:hover {
            border-color: var(--primary);
        }

        .grid-link i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        @media (max-width: 480px) {
            .grid-links {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="cover"
        style="@if($cover_path) background-image: url('{{ $cover_path }}') @else background: linear-gradient(45deg, #333, #555) @endif">
    </div>
    <div class="profile-container">
        <div class="header-card">
            @if($logo_path)
                <img src="{{ $logo_path }}" class="logo">
            @else
                <div class="logo"
                    style="display:flex;align-items:center;justify-content:center;background:#eee;font-size:2rem;color:#777;">
                    {{ substr($name, 0, 1) }}
                </div>
            @endif

            <div class="info">
                <h1>{{ $name }}</h1>
                @if($website)
                    <a href="{{ $website }}" target="_blank" class="website">{{ $website }}</a>
                @endif
            </div>
        </div>

        <div class="grid-links">
            @foreach($links as $link)
                @if(($link['platform'] ?? '') === 'text')
                    <div class="grid-link"
                        style="grid-column: 1 / -1; display: block; cursor: default; pointer-events: none; border-left: 4px solid var(--primary);">
                        <p style="margin: 0; white-space: pre-wrap; font-size: 0.95rem; color: #555;">{{ $link['url'] }}</p>
                    </div>
                @else
                    @php
                        $platform = $link['platform'] ?? 'website';
                        $url = $link['url'];
                        $label = ucfirst($platform);
                        $icon = "fa-brands fa-{$platform}";
                        $target = "_blank";

                        if ($platform === 'website') {
                            $icon = "fa-solid fa-globe";
                        } elseif ($platform === 'phone') {
                            $icon = "fa-solid fa-phone";
                            $url = "tel:" . $link['url'];
                            $target = "_self";
                        } elseif ($platform === 'sms') {
                            $icon = "fa-solid fa-comment-sms";
                            $url = "sms:" . $link['url'];
                            $target = "_self";
                        }
                    @endphp
                    <a href="{{ $url }}" target="{{ $target }}" class="grid-link">
                        <i class="{{ $icon }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</body>

</html>