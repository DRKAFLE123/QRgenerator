<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrController;
use App\Http\Controllers\BioPageController;

Route::post('/generate-qr', [QrController::class, 'generate']);
Route::post('/create-bio', [BioPageController::class, 'store']);
