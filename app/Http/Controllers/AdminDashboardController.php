<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private function getStats()
    {
        return [
            'total' => \App\Models\BioPage::count(),
            'active' => \App\Models\BioPage::where('status', 'active')->count(),
            'inactive' => \App\Models\BioPage::where('status', 'inactive')->count(),
            'paid' => \App\Models\BioPage::where('payment_status', 'paid')->count(),
            'unpaid' => \App\Models\BioPage::where('payment_status', 'unpaid')->count(),
            'expiring_soon' => \App\Models\BioPage::where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', Carbon::now()->addDays(7))
                ->where('expires_at', '>', Carbon::now())
                ->count(),
        ];
    }

    public function index(Request $request)
    {
        // Statistics
        $stats = $this->getStats();

        // Fetch Bio Pages with search, sorting, and filters
        $query = \App\Models\BioPage::query();

        if ($request->has('search') && $request->search) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status) {
            if ($request->status === 'expiring') {
                $query->where('status', 'active')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', Carbon::now()->addDays(7))
                    ->where('expires_at', '>', Carbon::now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort by organization name - ensure ordering
        $query->orderBy('name', 'asc');

        $bioPages = $query->paginate(10);

        if ($request->ajax()) {
            return view('admin.partials.bio-table-rows', compact('bioPages'))->render();
        }

        $platforms = \App\Models\Platform::all();
        return view('admin.dashboard', compact('stats', 'bioPages', 'platforms'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        DB::table('bio_pages')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'stats' => $this->getStats()
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid'
        ]);

        DB::table('bio_pages')->where('id', $id)->update([
            'payment_status' => $request->payment_status,
            'updated_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Payment status updated successfully',
            'stats' => $this->getStats()
        ]);
    }

    public function renewSubscription(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|in:180,365'
        ]);

        $page = DB::table('bio_pages')->where('id', $id)->first();
        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        // If it's already expired or has no expiry, start from now
        // If it's still active, extend from current expiry
        $baseDate = ($page->expires_at && Carbon::now()->lessThan(Carbon::parse($page->expires_at)))
            ? Carbon::parse($page->expires_at)
            : Carbon::now();

        $newExpiry = $baseDate->addDays($request->days);

        DB::table('bio_pages')->where('id', $id)->update([
            'expires_at' => $newExpiry,
            'payment_status' => 'paid',
            'status' => 'active',
            'updated_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => "Subscription renewed for {$request->days} days",
            'stats' => $this->getStats(),
            'new_expiry' => $newExpiry->format('Y-m-d')
        ]);
    }

    public function updateExpiry(Request $request, $id)
    {
        $request->validate([
            'expiry_date' => 'required|date'
        ]);

        DB::table('bio_pages')->where('id', $id)->update([
            'expires_at' => $request->expiry_date,
            'updated_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Expiry date updated successfully',
            'stats' => $this->getStats()
        ]);
    }

    public function destroy($id)
    {
        $page = \App\Models\BioPage::find($id);

        if (!$page) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bio Page not found. ID: ' . $id
                ], 404);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Bio Page not found. ID: ' . $id);
        }

        if ($page->status === 'active') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete an active Bio Page. Please deactivate it first.'
                ], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Cannot delete an active Bio Page.');
        }

        $page->delete(); // Soft Delete

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bio Page archived successfully',
                'stats' => $this->getStats()
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Bio Page archived successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bio_pages,id'
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            $activeCount = \App\Models\BioPage::whereIn('id', $request->ids)
                ->where('status', 'active')
                ->count();

            if ($activeCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active Bio Pages. Please deactivate them first.'
                ], 403);
            }

            \App\Models\BioPage::whereIn('id', $request->ids)->delete(); // Soft Delete

            return response()->json([
                'success' => true,
                'message' => 'Selected pages archived successfully',
                'stats' => $this->getStats()
            ]);
        }

        return redirect()->back()->with('error', 'Invalid request format.');
    }

    public function downloadQr(Request $request, $id)
    {
        $page = DB::table('bio_pages')->find($id);

        if (!$page) {
            abort(404);
        }

        // Generate the URL for the Bio Page
        if (!empty($page->permalink)) {
            $url = url('/biopage/' . $page->permalink);
        } else {
            $url = url('/bio/' . $page->id);
        }

        $format = $request->query('format', 'svg');
        $allowedFormats = ['svg', 'png', 'jpeg', 'pdf'];

        if (!in_array($format, $allowedFormats)) {
            $format = 'svg';
        }

        $qrBuilder = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(500)->color(0, 0, 0);

        if ($format === 'pdf') {
            // Check if DOMPDF is installed
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return response()->json(['error' => 'PDF generation library (dompdf) not installed.'], 500);
            }

            // Generate QR as base64 image to embed in PDF
            $qrImage = base64_encode($qrBuilder->format('svg')->generate($url));

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf.qr', ['qrImage' => $qrImage, 'name' => $page->name, 'url' => $url]);
            return $pdf->download('qr-' . $page->name . '.pdf');
        }

        // For image formats
        $response = $qrBuilder->format($format)->generate($url);

        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
        ];

        return response($response)
            ->header('Content-Type', $mimeTypes[$format])
            ->header('Content-Disposition', 'attachment; filename="qr-' . $page->name . '.' . $format . '"');
    }
    public function updateContent(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'links' => 'required|string', // JSON string
            'theme' => 'nullable|string|in:modern,vibrant,business',
            'website' => 'nullable|url',
        ]);

        $updateData = [
            'name' => $request->name,
            'links' => $request->links,
            'theme' => $request->theme,
            'website' => $request->website,
            'updated_at' => Carbon::now()
        ];

        // Handle file uploads if present (optional, usually admin might not update images often, but good to have)
        if ($request->hasFile('logo')) {
            $updateData['logo_path'] = $request->file('logo')->store('uploads/logos', 'public');
        }
        if ($request->hasFile('cover')) {
            $updateData['cover_path'] = $request->file('cover')->store('uploads/covers', 'public');
        }

        // We can also update colors if passed
        if ($request->has('color'))
            $updateData['color'] = $request->color;
        if ($request->has('bg_color'))
            $updateData['bg_color'] = $request->bg_color;

        DB::table('bio_pages')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Bio Page details updated successfully',
            'stats' => $this->getStats()
        ]);
    }
    public function getAnalytics($id)
    {
        $totalVisits = DB::table('bio_page_analytics')->where('bio_page_id', $id)->count();

        // Get visits for the last 7 days
        $visits = DB::table('bio_page_analytics')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('bio_page_id', $id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        // Device Breakdown
        $devices = DB::table('bio_page_analytics')
            ->select('device_type', DB::raw('count(*) as count'))
            ->where('bio_page_id', $id)
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Top Locations (City, Country)
        $locations = DB::table('bio_page_analytics')
            ->select('city', 'country', DB::raw('count(*) as count'))
            ->where('bio_page_id', $id)
            ->whereNotNull('country')
            ->groupBy('city', 'country')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'total_visits' => $totalVisits,
            'chart_data' => $visits,
            'device_data' => $devices,
            'location_data' => $locations
        ]);
    }

    // --- Platform Management ---

    public function storePlatform(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:platforms,key',
            'label' => 'required|string',
            'icon' => 'required|string',
            'type' => 'required|string',
        ]);

        \App\Models\Platform::create($request->all());

        return response()->json(['success' => true, 'message' => 'Platform added successfully']);
    }

    public function updatePlatform(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string',
            'icon' => 'required|string',
            'type' => 'required|string',
        ]);

        $platform = \App\Models\Platform::findOrFail($id);
        $platform->update($request->all());

        return response()->json(['success' => true, 'message' => 'Platform updated successfully']);
    }

    public function deletePlatform($id)
    {
        $platform = \App\Models\Platform::findOrFail($id);
        $platform->delete();

        return response()->json(['success' => true, 'message' => 'Platform deleted successfully']);
    }

    public function exportAnalytics($id)
    {
        $page = DB::table('bio_pages')->find($id);

        if (!$page) {
            abort(404);
        }

        $analytics = DB::table('bio_page_analytics')
            ->where('bio_page_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'analytics-' . ($page->permalink ?? $page->id) . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($analytics) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, ['Date', 'Time', 'IP Address', 'Device', 'Browser', 'OS', 'Location']);

            // CSV Data
            foreach ($analytics as $record) {
                $datetime = Carbon::parse($record->created_at);
                $location = ($record->city ?? 'Unknown') . ', ' . ($record->country ?? 'Unknown');
                fputcsv($file, [
                    $datetime->format('Y-m-d'),
                    $datetime->format('H:i:s'),
                    $record->ip_address ?? 'N/A',
                    $record->device_type ?? 'N/A',
                    $record->browser ?? 'N/A',
                    $record->os ?? 'N/A',
                    $location
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
