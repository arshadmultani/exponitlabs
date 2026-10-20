<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\LlmsTxt;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Whether observer should execute during unit/feature test runs.
     * Kept false by default to prevent tests from overwriting public/llms.txt.
     */
    public static bool $enabledInTests = false;

    /**
     * Handle the Product "saved" event (covers created and updated).
     */
    public function saved(Product $product): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->syncLlmsFiles();
    }

    /**
     * Handle the Product "forceDeleted" event.
     */
    public function forceDeleted(Product $product): void
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
