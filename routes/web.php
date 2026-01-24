<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrController;
use App\Http\Controllers\BioPageController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// Admin Authentication
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/debug-logo', function () {
    $files = \Illuminate\Support\Facades\Storage::disk('public')->allFiles();
    $publicPath = public_path('storage');
    $storagePath = storage_path('app/public');
    $linkExists = file_exists($publicPath) ? 'Yes' : 'No';
    $isLink = is_link($publicPath) ? 'Yes' : 'No';

    // Fetch first bio page
    $bioPage = \App\Models\BioPage::first();

    return [
        'app_url' => env('APP_URL'),
        'asset_url_test' => asset('storage/test.txt'),
        'public_storage_path' => $publicPath,
        'storage_app_public_path' => $storagePath,
        'link_exists' => $linkExists,
        'is_link' => $isLink,
        'bio_page_logo_path_in_db' => $bioPage ? $bioPage->logo_path : 'No BioPage Found',
        'generated_logo_url' => $bioPage ? asset('storage/' . $bioPage->logo_path) : null,
        'logo_file_exists_on_disk' => $bioPage ? (\Illuminate\Support\Facades\Storage::disk('public')->exists($bioPage->logo_path) ? 'Yes' : 'No') : 'N/A',
        'files_in_public_disk_count' => count($files),
    ];
});

Route::redirect('/admin', '/admin/login');

// Admin Dashboard (Protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/bio/{id}/status', [AdminDashboardController::class, 'updateStatus'])->name('admin.bio.status');
    Route::patch('/bio/{id}/payment', [AdminDashboardController::class, 'updatePayment'])->name('admin.bio.payment');
    Route::post('/bio/{id}/renew', [AdminDashboardController::class, 'renewSubscription'])->name('admin.bio.renew');
    Route::patch('/bio/{id}/expiry', [AdminDashboardController::class, 'updateExpiry'])->name('admin.bio.update-expiry');
    Route::post('/bio/{id}/update', [AdminDashboardController::class, 'updateContent'])->name('admin.bio.update-content');
    Route::delete('/bio/{id}', [AdminDashboardController::class, 'destroy'])->name('admin.bio.delete');
    Route::post('/bio/bulk-delete', [AdminDashboardController::class, 'bulkDestroy'])->name('admin.bio.bulk-delete');
    Route::get('/bio/{id}/download-qr', [AdminDashboardController::class, 'downloadQr'])->name('admin.bio.download-qr');
    Route::get('/bio/{id}/analytics', [AdminDashboardController::class, 'getAnalytics'])->name('admin.bio.analytics');
    Route::get('/bio/{id}/analytics/export', [AdminDashboardController::class, 'exportAnalytics'])->name('admin.bio.analytics.export');
});

// Bio Page View
Route::get('/biopage/{permalink}', [BioPageController::class, 'show']);

// API Routes are now in api.php
