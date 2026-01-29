<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

class BioPageController extends Controller
{
    private function parseUserAgent($userAgent)
    {
        $device = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $device = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $device = 'mobile';
        } elseif (preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent)) {
            $device = 'robot';
        }

        $browser = 'unknown';
        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Netscape/i', $userAgent)) {
            $browser = 'Netscape';
        }

        $os = 'unknown';
        if (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'Mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $os = 'Windows';
        } else if (preg_match('/android/i', $userAgent)) {
            $os = "Android";
        } else if (preg_match('/iphone/i', $userAgent)) {
            $os = "iPhone";
        }

        return ['device' => $device, 'browser' => $browser, 'os' => $os];
    }

    private function getLocationFromIp($ip)
    {
        // Skip local IP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['city' => 'Localhost', 'country' => 'Localhost'];
        }

        try {
            // Use a free IP API with a timeout to prevent hanging
            $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=city,country", false, stream_context_create(['http' => ['timeout' => 2]]));
            if ($json) {
                $data = json_decode($json, true);
                if ($data && ($data['status'] ?? '') === 'success') {
                    return ['city' => $data['city'], 'country' => $data['country']];
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return ['city' => 'Unknown', 'country' => 'Unknown'];
    }

    private function generatePermalink($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (DB::table('bio_pages')->where('permalink', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'links' => 'required|string',
            'color' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'theme' => 'nullable|string|in:modern,vibrant,business',
            'logo' => 'nullable|image|max:10240',
            'cover' => 'nullable|image|max:10240',
            'website' => 'nullable|url',
        ]);

        $id = Str::uuid()->toString();

        // Generate Permalinks
        $permalink = $this->generatePermalink($validated['name']);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('uploads/logos', 'public');
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('uploads/covers', 'public');
        }

        $linksInput = $request->input('links');
        if (is_string($linksInput)) {
            $links = $linksInput;
        } else {
            $links = json_encode($linksInput);
        }

        DB::table('bio_pages')->insert([
            'id' => $id,
            'name' => $validated['name'],
            'permalink' => $permalink,
            'links' => $links,
            'color' => $validated['color'] ?? '#FF6B6B',
            'bg_color' => $validated['bg_color'] ?? '#F8F9FA',
            'theme' => $validated['theme'] ?? 'modern',
            'logo_path' => $logoPath,
            'cover_path' => $coverPath,
            'website' => $validated['website'] ?? null,
            'status' => 'active', // Default to active
            'payment_status' => 'unpaid', // Default to unpaid
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'bio_id' => $id,
            'bio_url' => url('/biopage/' . $permalink)
        ]);
    }

    public function show($id)
    {
        // Try to find by permalink first, then by ID
        $page = DB::table('bio_pages')->where('permalink', $id)->first();

        if (!$page) {
            $page = DB::table('bio_pages')->find($id);
        }

        if (!$page) {
            abort(404, 'Bio Page not found');
        }

        // Check if page is active
        if ($page->status !== 'active') {
            abort(404, 'Bio Page is currently inactive');
        }

        // Log Analytics
        // Log Analytics
        try {
            $userAgent = request()->userAgent();
            $ip = request()->ip();

            $uaData = $this->parseUserAgent($userAgent);
            $geoData = $this->getLocationFromIp($ip);

            DB::table('bio_page_analytics')->insert([
                'bio_page_id' => $page->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_type' => $uaData['device'],
                'browser' => $uaData['browser'],
                'os' => $uaData['os'],
                'city' => $geoData['city'],
                'country' => $geoData['country'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail logging to avoid breaking the page
        }

        $links = json_decode($page->links, true);
        $theme = $page->theme ?? 'modern';
        $viewName = 'bio-page-' . $theme;

        if (!view()->exists($viewName)) {
            $viewName = 'bio-page';
        }

        // Fetch platforms for icon lookup
        $platforms = \App\Models\Platform::where('is_active', true)->get()->keyBy('key');

        return view($viewName, [
            'name' => $page->name,
            'links' => $links,
            'color' => $page->color,
            'bg_color' => $page->bg_color,
            'logo_path' => $page->logo_path ? asset('storage/' . $page->logo_path) : null,
            'cover_path' => $page->cover_path ? asset('storage/' . $page->cover_path) : null,
            'website' => $page->website,
            'platforms' => $platforms
        ]);
    }
}
