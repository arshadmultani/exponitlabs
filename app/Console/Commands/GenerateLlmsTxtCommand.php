<?php

namespace App\Console\Commands;

use App\Support\LlmsTxt;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:generate-llms')]
#[Description('Generate static public/llms.txt and public/llms-full.txt files from the active catalog')]
class GenerateLlmsTxtCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Generating llms.txt and llms-full.txt files...');

        LlmsTxt::saveToDisk();

        $this->components->info('Successfully written public/llms.txt and public/llms-full.txt.');

        return self::SUCCESS;
    }
}
