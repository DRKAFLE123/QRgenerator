<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    private function getStats()
    {
        return [
            'total' => DB::table('bio_pages')->count(),
            'active' => DB::table('bio_pages')->where('status', 'active')->count(),
            'inactive' => DB::table('bio_pages')->where('status', 'inactive')->count(),
            'paid' => DB::table('bio_pages')->where('payment_status', 'paid')->count(),
            'unpaid' => DB::table('bio_pages')->where('payment_status', 'unpaid')->count(),
            'expiring_soon' => DB::table('bio_pages')
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(7))
                ->where('expires_at', '>', now())
                ->count(),
        ];
    }

    public function index(Request $request)
    {
        // Statistics
        $stats = $this->getStats();

        // Fetch Bio Pages with search, sorting, and filters
        $query = DB::table('bio_pages');

        if ($request->has('search') && $request->search) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status) {
            if ($request->status === 'expiring') {
                $query->where('status', 'active')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now()->addDays(7))
                    ->where('expires_at', '>', now());
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

        return view('admin.dashboard', compact('stats', 'bioPages'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        DB::table('bio_pages')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now()
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
            'updated_at' => now()
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
        $baseDate = ($page->expires_at && now()->lessThan($page->expires_at))
            ? \Carbon\Carbon::parse($page->expires_at)
            : now();

        $newExpiry = $baseDate->addDays($request->days);

        DB::table('bio_pages')->where('id', $id)->update([
            'expires_at' => $newExpiry,
            'payment_status' => 'paid',
            'status' => 'active',
            'updated_at' => now()
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
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Expiry date updated successfully',
            'stats' => $this->getStats()
        ]);
    }

    public function destroy($id)
    {
        DB::table('bio_pages')->where('id', $id)->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bio Page deleted successfully',
                'stats' => $this->getStats()
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Bio Page deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bio_pages,id'
        ]);

        DB::table('bio_pages')->whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected pages deleted successfully',
            'stats' => $this->getStats()
        ]);
    }

    public function downloadQr(Request $request, $id)
    {
        $page = DB::table('bio_pages')->find($id);

        if (!$page) {
            abort(404);
        }

        // Generate the URL for the Bio Page
        $url = url('/bio/' . $page->id);

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
}
