<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * The /insights dashboard reads the pre-built `insights` SQLite store, which is
 * a local build artifact (not committed, not seeded). These tests assert the
 * routes wire up, and exercise the live endpoints only when the store exists so
 * the suite never goes red on a machine that hasn't run `insights:import`.
 */
function insightsStoreBuilt(): bool
{
    $path = config('database.connections.insights.database');

    return is_string($path) && file_exists($path) && filesize($path) > 0;
}

it('registers the insights routes', function () {
    expect(Route::has('insights.index'))->toBeTrue();
    expect(Route::has('insights.data'))->toBeTrue();
});

it('renders the dashboard as a noindex internal page', function () {
    if (! insightsStoreBuilt()) {
        $this->markTestSkipped('insights.sqlite not built — run `php artisan insights:import`.');
    }

    $this->actingAs(User::factory()->create())
        ->get('/insights')
        ->assertOk()
        ->assertSee('Market insights')
        ->assertSee('noindex', false);
});

it('blocks guests from the gated dashboard', function () {
    $this->get('/insights')->assertRedirect('/');
    $this->getJson('/insights/data')->assertUnauthorized();
});

it('returns sliced KPIs + trend + top lists as JSON', function () {
    if (! insightsStoreBuilt()) {
        $this->markTestSkipped('insights.sqlite not built — run `php artisan insights:import`.');
    }

    $res = $this->actingAs(User::factory()->create())
        ->getJson('/insights/data?q=PARACETAMOL');

    $res->assertOk()->assertJsonStructure([
        'kpi' => ['mat', 'prev', 'growth', 'share', 'packs'],
        'trend' => [['label', 'value']],
        'top' => ['molecule', 'brand', 'company', 'group'],
        'packs',
    ]);

    expect($res->json('kpi.packs'))->toBeGreaterThan(0);
});
