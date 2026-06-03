<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Raise Livewire's temporary-upload cap (default 12 MB) so AR videos can
        // be uploaded. Kept here to avoid publishing the whole livewire config.
        // Mirrors the FileUpload ->maxSize(50 MB) on the AR creative form.
        config()->set('livewire.temporary_file_upload.rules', ['required', 'file', 'max:51200']);
    }
}
