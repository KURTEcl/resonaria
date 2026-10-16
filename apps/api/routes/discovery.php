<?php

use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [DiscoveryController::class, 'sitemap']);
Route::get('/robots.txt', [DiscoveryController::class, 'robots']);
Route::get('/llms.txt', [DiscoveryController::class, 'llms']);
Route::get('/llms-full.txt', [DiscoveryController::class, 'llmsFull']);
Route::get('/google{token}.html', [DiscoveryController::class, 'googleVerification'])
    ->where('token', '[A-Za-z0-9_-]+');
