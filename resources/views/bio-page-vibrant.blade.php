<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta title="{{ $name }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:
                {{ $color ?? '#FF6B6B' }}
            ;
            --bg:
                {{ $bg_color ?? '#2D3436' }}
            ;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? '#2D3436' : 'white' }}
            ;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            @if($cover_path)
                background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ $cover_path }}');
                background-size: cover;
                background-position: center;
            @else background: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 20%);
            @endif
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 40px 20px;
            text-align: center;
        }

        .logo-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 25px;
        }

        .logo {
            width: 110px;
            height: 110px;
            border-radius: 30px;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            background: white;
        }

        h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .website {
            color:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? 'var(--primary)' : 'rgba(255, 255, 255, 0.7)' }}
            ;
            text-decoration: none;
            margin: 10px 0 40px;
            display: inline-block;
            background:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.1)' }}
            ;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .link-card {
            background:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? 'white' : 'rgba(255, 255, 255, 0.1)' }}
            ;
            backdrop-filter: blur(10px);
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? '#2D3436' : 'white' }}
            ;
            transition: all 0.3s;
            border: 1px solid
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? '#eee' : 'rgba(255, 255, 255, 0.05)' }}
            ;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .link-card:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.02);
            border-color: var(--primary);
        }

        .icon-box {
            width: 40px;
            height: 40px;
            background:
                {{ (str_starts_with($bg_color ?? '', '#f') || str_starts_with($bg_color ?? '', '#F')) ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.2)' }}
            ;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .link-text {
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Force contrast if cover is present */
        @if($cover_path)
            body {
                color: white !important;
            }

            .website {
                color: rgba(255, 255, 255, 0.7) !important;
                background: rgba(255, 255, 255, 0.1) !important;
            }

            .link-card {
                background: rgba(255, 255, 255, 0.1) !important;
                color: white !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }

            .icon-box {
                background: rgba(255, 255, 255, 0.2) !important;
            }

        @endif
    </style>
</head>

<body>
    <div class="bg-shapes"></div>
    <div class="card">
        <div class="logo-wrap">
            @if($logo_path)
                <img src="{{ $logo_path }}" class="logo">
            @else
                <div class="logo" style="display:flex;align-items:center;justify-content:center;color:#333;font-size:2rem;">
                    {{ substr($name, 0, 1) }}
                </div>
            @endif
        </div>

        <h1>{{ $name }}</h1>
        @if($website)
            <a href="{{ $website }}" target="_blank" class="website">{{ parse_url($website, PHP_URL_HOST) }}</a>
        @endif

        <div style="margin-top:20px;">
            @foreach($links as $link)
                @if(($link['platform'] ?? '') === 'text')
                    <div class="link-card"
                        style="display: block; text-align: left; cursor: default; pointer-events: none; background: rgba(255,255,255,0.15);">
                        <p style="margin: 0; font-size: 1rem; line-height: 1.6; white-space: pre-wrap;">{{ $link['url'] }}</p>
                    </div>
                @else
                    @php
                        $platformKey = $link['platform'] ?? 'website';
                        $url = $link['url'];

                        // Fallback lookup
                        $platformData = $platforms[$platformKey] ?? $platforms['website'] ?? null;

                        // Logic for label, icon, and type
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
                    <a href="{{ $url }}" target="{{ $target }}" class="link-card">
                        <div class="icon-box"><i class="{{ $icon }}"></i></div>
                        <span class="link-text">{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</body>

</html>