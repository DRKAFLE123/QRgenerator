<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'data' => 'required',
            'color' => 'nullable',
            'bg_color' => 'nullable',
            'size' => 'nullable|integer',
        ]);

        $data = $request->input('data');
        $color = $this->hexToRgb($request->input('color', '#000000'));
        $bgColor = $this->hexToRgb($request->input('bg_color', '#FFFFFF'));
        $size = $request->input('size', 200);

        // SimpleSoftwareIO QrCode generation
        // Note: format('png') requires imagick or gd.
        // We return base64 data uri.

        try {
            $qr = QrCode::format('svg')
                ->size($size * 10)
                ->color($color[0], $color[1], $color[2])
                ->backgroundColor($bgColor[0], $bgColor[1], $bgColor[2])
                ->errorCorrection('M')
                ->generate($data);

            // $qr is a string (XML) for SVG.
            $base64 = base64_encode($qr);
            return response()->json(['qr_image' => 'data:image/svg+xml;base64,' . $base64]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    private function hexToRgb($hex)
    {
        $hex = lstrip($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return [$r, $g, $b];
    }
}

function lstrip($str, $char)
{
    return ltrim($str, $char);
}
