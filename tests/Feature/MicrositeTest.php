<?php

use App\Filament\Resources\Microsites\Pages\CreateMicrosite;
use App\Filament\Resources\Microsites\Pages\EditMicrosite;
use App\Filament\Resources\Microsites\RelationManagers\ReviewsRelationManager;
use App\Models\Doctor;
use App\Models\Microsite;
use App\Models\Review;
use App\Models\Showcase;
use App\Models\User;
use Livewire\Livewire;

// ---------------------------------------------------------------------------
// Public page
// ---------------------------------------------------------------------------

it('renders an active microsite with doctor details and approved reviews', function () {
    $microsite = Microsite::factory()->create();
    $doctor = $microsite->doctor;

    Review::factory()->approved()->create([
        'doctor_id' => $doctor->id,
        'reviewer_name' => 'Happy Patient',
        'review_text' => 'Excellent care',
        'rating' => 5,
    ]);

    $this->get(route('microsite.show', $microsite))
        ->assertOk()
        ->assertSeeText('Dr. '.$doctor->name)
        ->assertSeeText($doctor->specialty)
        ->assertSeeText('Happy Patient')
        ->assertSeeText('Excellent care')
        ->assertSee('★', false);
});

it('hides pending and rejected reviews from the public page', function () {
    $microsite = Microsite::factory()->create();

    Review::factory()->create([
        'doctor_id' => $microsite->doctor_id,
        'reviewer_name' => 'Pending Pat',
        'review_text' => 'waiting approval',
    ]);
    Review::factory()->rejected()->create([
        'doctor_id' => $microsite->doctor_id,
        'reviewer_name' => 'Rejected Ray',
        'review_text' => 'was rejected',
    ]);

    $this->get(route('microsite.show', $microsite))
        ->assertOk()
        ->assertDontSeeText('Pending Pat')
        ->assertDontSeeText('Rejected Ray')
        ->assertSeeText('No reviews yet');
});

it('shows an inactive notice when the microsite is not active', function () {
    $microsite = Microsite::factory()->inactive()->create();

    $this->get(route('microsite.show', $microsite))
        ->assertOk()
        ->assertSeeText('This site is currently inactive.');
});

it('returns the doctor 404 view for an unknown slug', function () {
    $this->get('/dr/does-not-exist')
        ->assertNotFound()
        ->assertSeeText('The page you\'re looking for does not exist. Please check the URL.');
});

it('groups showcases by media type on the public page', function () {
    $microsite = Microsite::factory()->create();

    Showcase::factory()->text()->create(['doctor_id' => $microsite->doctor_id, 'title' => 'About Me']);
    Showcase::factory()->image()->create(['doctor_id' => $microsite->doctor_id, 'title' => 'Clinic Photo']);

    $this->get(route('microsite.show', $microsite))
        ->assertOk()
        ->assertSeeText('About Me')
        ->assertSeeText('Clinic Photo')
        ->assertSeeText('Gallery');
});

// ---------------------------------------------------------------------------
// Model behaviour
// ---------------------------------------------------------------------------

it('generates unique url slugs from the doctor name', function () {
    $a = Microsite::uniqueUrl('Jane Doe');
    $b = Microsite::uniqueUrl('Jane Doe');

    expect($a)->toMatch('/^jane-doe-[a-z0-9]{6}$/')
        ->and($a)->not->toBe($b);
});

it('computes whole years of experience or null', function () {
    expect((new Doctor(['practice_since' => null]))->experienceYears())->toBeNull()
        ->and((new Doctor(['practice_since' => now()->subYears(8)]))->experienceYears())->toBe(8);
});

it('reads bg_color out of design_settings', function () {
    $microsite = Microsite::factory()->create(['design_settings' => ['bg_color' => '#abcdef']]);

    expect($microsite->bg_color)->toBe('#abcdef');
});

it('cascades review and showcase deletion when the microsite is deleted', function () {
    $microsite = Microsite::factory()->create();
    Review::factory()->count(2)->create(['doctor_id' => $microsite->doctor_id]);
    Showcase::factory()->text()->count(2)->create(['doctor_id' => $microsite->doctor_id]);

    $microsite->delete();

    expect(Review::count())->toBe(0)
        ->and(Showcase::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Filament admin
// ---------------------------------------------------------------------------

it('auto-generates a url slug when creating a microsite in the panel', function () {
    $this->actingAs(User::factory()->create());
    $doctor = Doctor::factory()->create(['name' => 'Gregory House']);

    Livewire::test(CreateMicrosite::class)
        ->fillForm([
            'doctor_id' => $doctor->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Microsite::first()->url)->toMatch('/^gregory-house-[a-z0-9]{6}$/');
});

it('approves a review through the reviews relation manager', function () {
    $this->actingAs(User::factory()->create());
    $microsite = Microsite::factory()->create();
    $review = Review::factory()->create(['doctor_id' => $microsite->doctor_id]);

    Livewire::test(ReviewsRelationManager::class, [
        'ownerRecord' => $microsite,
        'pageClass' => EditMicrosite::class,
    ])->callTableAction('approve', $review);

    $review->refresh();
    expect($review->status)->toBe('approved')
        ->and($review->verified_at)->not->toBeNull();
});
