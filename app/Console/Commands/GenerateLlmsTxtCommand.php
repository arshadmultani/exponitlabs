<?php

namespace App\Console\Commands;

use App\Support\LlmsTxt;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:generate-llms {--dir= : Output directory (defaults to public_path())}')]
#[Description('Generate static public/llms.txt and public/llms-full.txt files from the active catalog')]
class GenerateLlmsTxtCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = $this->option('dir') ? (string) $this->option('dir') : public_path();

        $this->components->info("Generating llms.txt and llms-full.txt files into {$dir}...");

        LlmsTxt::saveToDisk($dir);

        $this->components->info('Successfully written llms.txt and llms-full.txt.');

        return self::SUCCESS;
    }
}
