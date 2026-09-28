<?php

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest is redirected to login when visiting console profile page', function () {
    $this->get(route('filament.console.auth.profile'))
        ->assertRedirect(route('filament.console.auth.login'));
});

test('authenticated user can view the console profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.console.auth.profile'))
        ->assertOk();
});

test('user getFilamentAvatarUrl returns null when no avatar set', function () {
    $user = User::factory()->make(['avatar_url' => null]);

    expect($user->getFilamentAvatarUrl())->toBeNull();
});

test('user getFilamentAvatarUrl returns public storage url when avatar is uploaded', function () {
    $user = User::factory()->make(['avatar_url' => 'avatars/profile.jpg']);

    expect($user->getFilamentAvatarUrl())
        ->toBe(Storage::disk('public')->url('avatars/profile.jpg'));
});

test('user getFilamentAvatarUrl returns url directly when full url is stored', function () {
    $user = User::factory()->make(['avatar_url' => 'https://example.com/avatar.png']);

    expect($user->getFilamentAvatarUrl())
        ->toBe('https://example.com/avatar.png');
});

test('authenticated user can update profile details', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('data.name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('authenticated user can upload avatar on profile page', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('data.avatar_url', $file)
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->avatar_url)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_url);
});
