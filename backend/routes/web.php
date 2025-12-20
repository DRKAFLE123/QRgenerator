<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrController;
use App\Http\Controllers\BioPageController;

Route::get('/', function () {
    return response()->json(['message' => 'QR Code Generator API is running']);
});

// API Routes
Route::post('/api/generate-qr', [QrController::class, 'generate']);
Route::post('/api/create-bio', [BioPageController::class, 'store']); // Create Bio Page
Route::get('/bio/{id}', [BioPageController::class, 'show']); // View Bio Page
