<?php

use App\Filament\Resources\ArCreatives\Pages\EditArCreative;
use App\Models\ArCreative;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Put the marker + video on a faked disk so Filament's FileUpload fields hydrate
// (and thus pass "required") when we drive the Edit page form in a test.
function fakePublicFiles(): void
{
    Storage::fake('public');
    Storage::disk('public')->put('ar/markers/marker.png', 'img');
    Storage::disk('public')->put('ar/videos/clip.mp4', 'vid');
}

function makeReadyCreative(array $overrides = []): ArCreative
{
    return ArCreative::create(array_merge([
        'name' => 'Calpol Box Reveal',
        'status' => 'published',
        'play_mode' => 'loop',
        'marker_image_path' => 'ar/markers/marker.png',
        'video_path' => 'ar/videos/clip.mp4',
        'mind_file_path' => 'ar/mind/calpol-box-reveal.mind',
        'tracking_score' => 85,
    ], $overrides));
}

it('builds a readable slug with an unguessable random token', function () {
    $a = ArCreative::create(['name' => 'Box Reveal', 'status' => 'draft', 'play_mode' => 'loop']);
    $b = ArCreative::create(['name' => 'Box Reveal', 'status' => 'draft', 'play_mode' => 'loop']);

    // Readable prefix + a 6-char random token: "box-reveal-xxxxxx".
    expect($a->slug)->toStartWith('box-reveal-');
    expect($a->slug)->toMatch('/^box-reveal-[a-z0-9]{6}$/');
    // Same name, different token — never collides.
    expect($a->slug)->not->toBe($b->slug);
});

it('reports readiness only when image, video and mind file are all present', function () {
    $ready = makeReadyCreative();
    $missing = ArCreative::create(['name' => 'Half Done', 'status' => 'draft', 'play_mode' => 'loop']);

    expect($ready->isReady())->toBeTrue();
    expect($missing->isReady())->toBeFalse();
});

it('maps the tracking score to a trackability tier', function () {
    expect((new ArCreative(['tracking_score' => null]))->trackabilityTier())->toBeNull();
    expect((new ArCreative(['tracking_score' => 20]))->trackabilityTier())->toBe('poor');
    expect((new ArCreative(['tracking_score' => 20]))->isTrackable())->toBeFalse();
    expect((new ArCreative(['tracking_score' => 45]))->trackabilityTier())->toBe('fair');
    expect((new ArCreative(['tracking_score' => 45]))->isTrackable())->toBeTrue();
    expect((new ArCreative(['tracking_score' => 80]))->trackabilityTier())->toBe('good');
});

it('shows the AR page for a published, ready creative', function () {
    $creative = makeReadyCreative();

    $this->get(route('ar.show', $creative))
        ->assertOk()
        ->assertSee($creative->name, false)
        ->assertSee('ar-start-button', false);
});

it('hides the AR page for draft creatives from the public', function () {
    $creative = makeReadyCreative(['status' => 'draft']);

    $this->get(route('ar.show', $creative))->assertNotFound();
});

it('lets a signed-in admin preview a ready draft', function () {
    $creative = makeReadyCreative(['status' => 'draft']);

    $this->actingAs(User::factory()->create())
        ->get(route('ar.show', $creative))
        ->assertOk();
});

it('hides the AR page when the tracking file is missing', function () {
    $creative = makeReadyCreative(['mind_file_path' => null]);

    $this->get(route('ar.show', $creative))->assertNotFound();
});

it('rejects an unauthenticated compile upload', function () {
    $creative = makeReadyCreative(['mind_file_path' => null]);

    $this->post(route('ar.compile', $creative), [])->assertRedirect();
});

it('renders the Filament edit page with the in-browser compiler widget', function () {
    $user = User::factory()->create();
    $creative = makeReadyCreative();

    $this->actingAs($user)
        ->get("/ar-creatives/{$creative->slug}/edit")
        ->assertOk()
        ->assertSee('AR tracking file')
        ->assertSee('Re-compile tracking file');
});

it('stores the compiled mind file and tracking score for an admin', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $creative = ArCreative::create([
        'name' => 'New Reveal',
        'status' => 'draft',
        'play_mode' => 'loop',
        'marker_image_path' => 'ar/markers/marker.png',
    ]);

    $file = UploadedFile::fake()->create('target.mind', 12, 'application/octet-stream');

    $this->actingAs($user)
        ->post(route('ar.compile', $creative), [
            'mind' => $file,
            'tracking_score' => 88,
        ])
        ->assertOk()
        ->assertJson(['ok' => true, 'tracking_score' => 88]);

    $creative->refresh();

    expect($creative->mind_file_path)->toBe("ar/mind/{$creative->slug}.mind");
    expect($creative->tracking_score)->toBe(88);
    Storage::disk('public')->assertExists("ar/mind/{$creative->slug}.mind");
});

it('rejects a tracking score above 100', function () {
    $user = User::factory()->create();
    $creative = makeReadyCreative(['mind_file_path' => null, 'tracking_score' => null]);
    $file = UploadedFile::fake()->create('target.mind', 12, 'application/octet-stream');

    $this->actingAs($user)
        ->post(route('ar.compile', $creative), ['mind' => $file, 'tracking_score' => 250])
        ->assertSessionHasErrors('tracking_score');
});

it('blocks publishing a creative whose marker is not trackable', function () {
    fakePublicFiles();
    $user = User::factory()->create();
    $this->actingAs($user);
    $creative = makeReadyCreative(['status' => 'draft', 'tracking_score' => 20]);

    Livewire::test(EditArCreative::class, ['record' => $creative->getRouteKey()])
        ->fillForm(['status' => 'published'])
        ->call('save');

    expect($creative->fresh()->status)->toBe('draft');
});

it('allows publishing a trackable, ready creative', function () {
    fakePublicFiles();
    $user = User::factory()->create();
    $this->actingAs($user);
    $creative = makeReadyCreative(['status' => 'draft', 'tracking_score' => 85]);

    Livewire::test(EditArCreative::class, ['record' => $creative->getRouteKey()])
        ->fillForm(['status' => 'published'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($creative->fresh()->status)->toBe('published');
});
