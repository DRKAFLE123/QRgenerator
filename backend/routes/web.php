<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrController;
use App\Http\Controllers\BioPageController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    return response()->json(['message' => 'QR Code Generator API is running']);
});

// Admin Authentication
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::redirect('/admin', '/admin/login');

// Admin Dashboard (Protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/bio/{id}/status', [AdminDashboardController::class, 'updateStatus'])->name('admin.bio.status');
    Route::patch('/bio/{id}/payment', [AdminDashboardController::class, 'updatePayment'])->name('admin.bio.payment');
    Route::post('/bio/{id}/renew', [AdminDashboardController::class, 'renewSubscription'])->name('admin.bio.renew');
    Route::patch('/bio/{id}/expiry', [AdminDashboardController::class, 'updateExpiry'])->name('admin.bio.update-expiry');
    Route::delete('/bio/{id}', [AdminDashboardController::class, 'destroy'])->name('admin.bio.delete');
    Route::post('/bio/bulk-delete', [AdminDashboardController::class, 'bulkDestroy'])->name('admin.bio.bulk-delete');
    Route::get('/bio/{id}/download-qr', [AdminDashboardController::class, 'downloadQr'])->name('admin.bio.download-qr');
});

// API Routes
Route::post('/api/generate-qr', [QrController::class, 'generate']);
Route::post('/api/create-bio', [BioPageController::class, 'store']); // Create Bio Page
Route::get('/bio/{id}', [BioPageController::class, 'show']); // View Bio Page
