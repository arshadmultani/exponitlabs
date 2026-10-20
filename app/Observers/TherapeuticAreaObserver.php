<?php

namespace App\Observers;

use App\Models\TherapeuticArea;
use App\Support\LlmsTxt;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class TherapeuticAreaObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Whether observer should execute during unit/feature test runs.
     * Kept false by default to prevent tests from overwriting public/llms.txt.
     */
    public static bool $enabledInTests = false;

    /**
     * Handle the TherapeuticArea "saved" event (covers created and updated).
     */
    public function saved(TherapeuticArea $therapeuticArea): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the TherapeuticArea "deleted" event.
     */
    public function deleted(TherapeuticArea $therapeuticArea): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the TherapeuticArea "restored" event.
     */
    public function restored(TherapeuticArea $therapeuticArea): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the TherapeuticArea "forceDeleted" event.
     */
    public function forceDeleted(TherapeuticArea $therapeuticArea): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Regenerate public/llms.txt and public/llms-full.txt from the active catalog.
     */
    protected function syncLlmsFiles(): void
    {
        if (app()->runningUnitTests() && ! static::$enabledInTests) {
            return;
        }

        LlmsTxt::saveToDisk();
    }
}
