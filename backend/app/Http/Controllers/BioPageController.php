<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

class BioPageController extends Controller
{
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
        try {
            DB::table('bio_page_analytics')->insert([
                'bio_page_id' => $page->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
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
