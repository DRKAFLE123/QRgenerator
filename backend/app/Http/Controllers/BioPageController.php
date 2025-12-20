<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

class BioPageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'links' => 'required|string', // Changed to string because FormData sends stringified JSON or individual fields. Wait, FormData sends strings.
            // Actually, if using FormData, 'links' might be sent as JSON string or array.
            // If frontend sends `links: [...]` via FormData, it's tricky. Best to send `links` as JSON string.
            'color' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'theme' => 'nullable|string|in:modern,vibrant,business',
            'logo' => 'nullable|image|max:10240',
            'cover' => 'nullable|image|max:10240',
            'website' => 'nullable|url',
        ]);

        $id = Str::uuid()->toString();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('uploads/logos', 'public');
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('uploads/covers', 'public');
        }

        // Decode links if it's a JSON string, ensuring it's stored correctly.
        // If it came as valid JSON string, we can just store it directly or decode/encode to be safe.
        // Let's assume frontend sends a JSON string for links.
        $linksInput = $request->input('links');
        // valid json check? 
        if (is_string($linksInput)) {
            $links = $linksInput; // store as is, assuming valid json
        } else {
            $links = json_encode($linksInput);
        }

        DB::table('bio_pages')->insert([
            'id' => $id,
            'name' => $validated['name'],
            'links' => $links,
            'color' => $validated['color'] ?? '#FF6B6B',
            'bg_color' => $validated['bg_color'] ?? '#F8F9FA',
            'theme' => $validated['theme'] ?? 'modern',
            'logo_path' => $logoPath,
            'cover_path' => $coverPath,
            'website' => $validated['website'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'bio_id' => $id,
            'bio_url' => url('/bio/' . $id)
        ]);
    }

    public function show($id)
    {
        $page = DB::table('bio_pages')->find($id);

        if (!$page) {
            abort(404, 'Bio Page not found');
        }

        $links = json_decode($page->links, true);
        $theme = $page->theme ?? 'modern';
        $viewName = 'bio-page-' . $theme;

        // Fallback if view doesn't exist (though we will create them)
        if (!view()->exists($viewName)) {
            $viewName = 'bio-page'; // Fallback to original if needed, or default modern
        }

        return view($viewName, [
            'name' => $page->name,
            'links' => $links,
            'color' => $page->color,
            'bg_color' => $page->bg_color,
            'logo_path' => $page->logo_path ? asset('storage/' . $page->logo_path) : null,
            'cover_path' => $page->cover_path ? asset('storage/' . $page->cover_path) : null,
            'website' => $page->website
        ]);
    }
}
