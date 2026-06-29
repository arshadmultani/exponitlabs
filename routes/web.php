<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArController;
use Spatie\Honeypot\ProtectAgainstSpam;
use App\Http\Controllers\PageController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\MicrositeController;

// Public marketing website. Filament admin lives at /console, so "/" is ours.
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/products', [PageController::class, 'products'])->name('products.index');
Route::get('/products/{product:slug}', [PageController::class, 'product'])->name('products.show');
Route::get('/news', [PageController::class, 'news'])->name('news.index');
Route::get('/news/{post:slug}', [PageController::class, 'newsShow'])->name('news.show');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])
    ->middleware(ProtectAgainstSpam::class)
    ->name('contact.submit');

// Static regulatory pages (linked from the footer).
Route::view('/privacy-policy', 'pages.legal.privacy')->name('privacy');
Route::view('/terms', 'pages.legal.terms')->name('terms');
Route::view('/refund-policy', 'pages.legal.refund')->name('refund');

// PTR/PTS calculator
Route::view('/pricing-calculator', 'pages.pricing-calculator')->name('pricing-calculator');

// SEO: XML sitemap (DIY, no package).
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

// Public augmented-reality viewer.
// The QR code printed on a creative points here; only published creatives are visible.
Route::get('/ar/{creative}', [ArController::class, 'show'])->name('ar.show');

// Admin-only: receives the ".mind" tracking file compiled in the browser.
Route::middleware('auth')
    ->post('/admin/ar/{creative}/compile', [ArController::class, 'storeMind'])
    ->name('ar.compile');

// Public doctor website (microsite). Shared link / QR points here.
Route::get('/dr/{slug}', [MicrositeController::class, 'show'])->name('microsite.show');

// Internal pharma sales-audit dashboard. Reads the read-only `insights` SQLite
// store. Kept in its own group so a gate drops in here later — add
// ->middleware('auth') (or 'auth:web') to the group to lock it down.
Route::prefix('insights')->name('insights.')->group(function () {
    Route::get('/', [InsightsController::class, 'index'])->name('index');
    Route::get('/data', [InsightsController::class, 'data'])->name('data');
});
