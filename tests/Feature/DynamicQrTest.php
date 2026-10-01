<?php

use App\Filament\Resources\QrCodes\Pages\CreateQrCode;
use App\Filament\Resources\QrCodes\Pages\EditQrCode;
use App\Filament\Resources\QrCodes\Pages\ListQrCodes;
use App\Filament\Resources\QrCodes\Pages\ViewQrCode;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function () {
    // Process queued jobs immediately for synchronous assertion in feature tests
    config(['queue.default' => 'sync']);
});

it('redirects to destination url and records telemetry on scan', function () {
    $qrCode = QrCode::factory()->create([
        'destination_url' => 'https://example.com/target-landing-page',
        'is_active' => true,
    ]);

    $response = get(route('qr.redirect', ['code' => $qrCode->code]), [
        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        'CF-IPCountry' => 'US',
        'CF-IPCity' => 'New York',
    ]);

    $response->assertStatus(307);
    $response->assertRedirect('https://example.com/target-landing-page');
    $response->assertCookie("qr_visited_{$qrCode->id}");

    $qrCode->refresh();
    expect($qrCode->total_scans)->toBe(1)
        ->and($qrCode->unique_scans)->toBe(1)
        ->and($qrCode->last_scanned_at)->not->toBeNull();

    assertDatabaseHas(QrScan::class, [
        'qr_code_id' => $qrCode->id,
        'device_type' => 'mobile',
        'platform' => 'iOS',
        'browser' => 'Mobile Safari',
        'is_unique' => true,
        'country_code' => 'US',
    ]);
});

it('distinguishes between unique visitors and repeat scans', function () {
    $qrCode = QrCode::factory()->create([
        'destination_url' => 'https://example.com/promo',
    ]);

    // First scan: no cookie
    $res1 = get(route('qr.redirect', ['code' => $qrCode->code]), [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);
    $res1->assertStatus(307);

    // Second scan: with cookie
    $res2 = $this->withCookie("qr_visited_{$qrCode->id}", '1')
        ->get(route('qr.redirect', ['code' => $qrCode->code]), [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);
    $res2->assertStatus(307);

    $qrCode->refresh();
    expect($qrCode->total_scans)->toBe(2)
        ->and($qrCode->unique_scans)->toBe(1);

    expect(QrScan::where('qr_code_id', $qrCode->id)->count())->toBe(2);
    expect(QrScan::where('qr_code_id', $qrCode->id)->where('is_unique', true)->count())->toBe(1);
    expect(QrScan::where('qr_code_id', $qrCode->id)->where('is_unique', false)->count())->toBe(1);
});

it('routes smartly to iOS or Android URLs when configured', function () {
    $qrCode = QrCode::factory()->create([
        'destination_url' => 'https://example.com/web',
        'ios_url' => 'https://apps.apple.com/app/my-app/id123456',
        'android_url' => 'https://play.google.com/store/apps/details?id=com.myapp',
    ]);

    // Test iOS user agent
    $iosRes = get(route('qr.redirect', ['code' => $qrCode->code]), [
        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
    ]);
    $iosRes->assertRedirect('https://apps.apple.com/app/my-app/id123456');

    // Test Android user agent
    $androidRes = get(route('qr.redirect', ['code' => $qrCode->code]), [
        'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36',
    ]);
    $androidRes->assertRedirect('https://play.google.com/store/apps/details?id=com.myapp');

    // Test Desktop user agent
    $desktopRes = get(route('qr.redirect', ['code' => $qrCode->code]), [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);
    $desktopRes->assertRedirect('https://example.com/web');
});

it('redirects to fallback url when paused or expired', function () {
    $fallbackUrl = 'https://example.com/expired-campaign';

    $pausedQr = QrCode::factory()->create([
        'destination_url' => 'https://example.com/live',
        'fallback_url' => $fallbackUrl,
        'is_active' => false,
    ]);

    $res = get(route('qr.redirect', ['code' => $pausedQr->code]));
    $res->assertRedirect($fallbackUrl);

    $expiredQr = QrCode::factory()->create([
        'destination_url' => 'https://example.com/live',
        'fallback_url' => $fallbackUrl,
        'is_active' => true,
        'expires_at' => now()->subHour(),
    ]);

    $res2 = get(route('qr.redirect', ['code' => $expiredQr->code]));
    $res2->assertRedirect($fallbackUrl);
});

it('updates destination url immediately without changing code or qr image', function () {
    $qrCode = QrCode::factory()->create([
        'destination_url' => 'https://example.com/old-link',
    ]);

    $res1 = get(route('qr.redirect', ['code' => $qrCode->code]));
    $res1->assertRedirect('https://example.com/old-link');

    // Admin updates the URL from the dashboard
    $qrCode->update(['destination_url' => 'https://example.com/brand-new-link']);

    $res2 = get(route('qr.redirect', ['code' => $qrCode->code]));
    $res2->assertRedirect('https://example.com/brand-new-link');
});

it('downloads PNG and SVG QR codes', function () {
    $qrCode = QrCode::factory()->create();

    $pngRes = get(route('qr.download', ['code' => $qrCode->code, 'format' => 'png']));
    $pngRes->assertStatus(200);
    $pngRes->assertHeader('Content-Type', 'image/png');

    $svgRes = get(route('qr.download', ['code' => $qrCode->code, 'format' => 'svg']));
    $svgRes->assertStatus(200);
    $svgRes->assertHeader('Content-Type', 'image/svg+xml');
});

it('renders Filament list page and creates QR code via form', function () {
    actingAs(User::factory()->create());

    Livewire::test(ListQrCodes::class)
        ->assertSuccessful();

    Livewire::test(CreateQrCode::class)
        ->fillForm([
            'name' => 'Brochure Q4 2026',
            'code' => 'brochure2026',
            'destination_url' => 'https://exponitlabs.com/brochure',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(QrCode::class, [
        'name' => 'Brochure Q4 2026',
        'code' => 'brochure2026',
        'destination_url' => 'https://exponitlabs.com/brochure',
    ]);
});

it('renders Filament view page with analytics widgets and scan relation manager', function () {
    actingAs(User::factory()->create());
    $qrCode = QrCode::factory()->create();
    QrScan::factory()->count(3)->create(['qr_code_id' => $qrCode->id]);

    Livewire::test(ViewQrCode::class, ['record' => $qrCode->id])
        ->assertSuccessful();
});

it('edits a QR code via Filament edit page', function () {
    actingAs(User::factory()->create());
    $qrCode = QrCode::factory()->create(['destination_url' => 'https://example.com/original']);

    Livewire::test(EditQrCode::class, ['record' => $qrCode->id])
        ->fillForm([
            'destination_url' => 'https://example.com/updated-via-filament',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($qrCode->fresh()->destination_url)->toBe('https://example.com/updated-via-filament');
});
