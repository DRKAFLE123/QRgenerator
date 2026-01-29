<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:
                {{ $color ?? '#2d3436' }}
            ;
            --bg:
                {{ $bg_color ?? '#ffffff' }}
            ;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color:
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'white' : '#2D3436' }}
            ;
        }

        .cover-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display:
                {{ $cover_path ? 'block' : 'none' }}
            ;
        }

        .container {
            width: 100%;
            max-width: 480px;
            padding: 40px 20px;
            text-align: center;
            margin-top:
                {{ $cover_path ? '-50px' : '0' }}
            ;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'var(--bg)' : 'white' }}
            ;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .placeholder-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #dfe6e9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: #636e72;
            border: 5px solid white;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 1.8rem;
        }

        .website {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            display: block;
        }

        .links {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }

        .link-btn {
            background:
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'rgba(255,255,255,0.1)' : 'white' }}
            ;
            border: 1px solid
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'rgba(255,255,255,0.2)' : '#dfe6e9' }}
            ;
            padding: 16px;
            border-radius: 50px;
            text-decoration: none;
            color:
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'white' : '#2D3436' }}
            ;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .link-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
            background:
                {{ (str_starts_with($bg_color ?? '', '#2') || str_starts_with($bg_color ?? '', '#0')) ? 'rgba(255,255,255,0.2)' : 'white' }}
            ;
        }

        .footer {
            margin-top: 40px;
            font-size: 0.8rem;
            color: #b2bec3;
        }
    </style>
</head>

<body>
    @if($cover_path)
        <img src="{{ $cover_path }}" class="cover-img">
    @endif
    <div class="container">
        @if($logo_path)
            <img src="{{ $logo_path }}" alt="Logo" class="logo">
        @else
            <div class="placeholder-logo">{{ substr($name, 0, 1) }}</div>
        @endif

        <h1>{{ $name }}</h1>

        @if($website)
            <a href="{{ $website }}" target="_blank" class="website">
                <i class="fa-solid fa-link"></i> {{ parse_url($website, PHP_URL_HOST) }}
            </a>
        @endif

        <div class="links">
            @php
                $custom_links = array_filter($links ?? [], fn($l) => ($l['platform'] ?? '') === 'custom');
                $social_links = array_filter($links ?? [], fn($l) => ($l['platform'] ?? '') !== 'custom');
            @endphp

            @foreach($social_links as $link)
                @if(($link['platform'] ?? '') === 'text')
                    <div class="link-btn"
                        style="cursor: default; background: rgba(0,0,0,0.03); border: none; box-shadow: none; display: block; text-align: left; padding: 15px;">
                        <p style="margin: 0; font-weight: 400; line-height: 1.5; white-space: pre-wrap;">{{ $link['url'] }}</p>
                    </div>
                @else
                    @php
                        $platformKey = $link['platform'] ?? 'website';
                        $url = $link['url'];

                        // Fallback to website if platform not found in DB
                        $platformData = $platforms[$platformKey] ?? $platforms['website'] ?? null;

                        // If completely missing, define defaults
                        $label = !empty($link['label']) ? $link['label'] : ($platformData ? $platformData->label : ucfirst($platformKey));
                        $icon = $platformData ? $platformData->icon : 'fa-solid fa-globe';
                        $type = $platformData ? $platformData->type : 'url';
                        $target = "_blank";

                        if ($type === 'phone') {
                            $url = "tel:" . preg_replace('/[^0-9+]/', '', $link['url']);
                            $target = "_self";
                        } elseif ($type === 'sms') {
                            $url = "sms:" . preg_replace('/[^0-9+]/', '', $link['url']);
                            $target = "_self";
                        } elseif ($type === 'whatsapp') {
                            $url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $link['url']);
                            $target = "_blank";
                        }
                    @endphp
                    <a href="{{ $url }}" target="{{ $target }}" class="link-btn">
                        <i class="{{ $icon }}"></i> {{ $label }}
                    </a>
                @endif
            @endforeach

            @foreach($custom_links as $link)
                <a href="{{ $link['url'] }}" target="_blank" class="link-btn">
                    <i class="fa-solid fa-link"></i> {{ $link['label'] ?? 'Link' }}
                </a>
            @endforeach
        </div>

        <div class="footer">Using Free QR Generator</div>
    </div>
</body>

</html>