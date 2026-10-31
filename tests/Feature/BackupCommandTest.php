<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Symfony\Component\Console\Command\Command;

beforeEach(function () {
    Config::set('services.discord_backup.webhook', 'https://discord.com/api/webhooks/test/token');
    Config::set('services.discord_backup.excluded_directories', ['ar']);
    Config::set('services.discord_backup.max_file_size', 10 * 1024 * 1024);
});

it('creates backup zip excluding ar assets and sends to discord with success message', function () {
    Http::fake([
        'https://discord.com/api/webhooks/*' => Http::response(['id' => '123'], 200),
    ]);

    DiscordAlert::shouldReceive('message')->never();

    $testPublicPath = storage_path('app/public');
    $arTestFile = "{$testPublicPath}/ar/test_asset.txt";
    $normalTestFile = "{$testPublicPath}/news/test_news.txt";

    File::ensureDirectoryExists(dirname($arTestFile));
    File::ensureDirectoryExists(dirname($normalTestFile));
    File::put($arTestFile, 'AR asset content');
    File::put($normalTestFile, 'News content');

    try {
        $this->artisan('backup:discord')
            ->expectsOutput('Creating zip...')
            ->expectsOutputToContain('Sending backup (')
            ->expectsOutput('Backup sent to Discord successfully.')
            ->assertExitCode(Command::SUCCESS);

        Http::assertSent(function ($request) {
            $isDiscordUrl = str_contains($request->url(), 'discord.com/api/webhooks/test/token');
            $data = collect($request->data());

            // Verify payload JSON with success embed & message
            $payloadRaw = $data->firstWhere('name', 'payload_json')['contents'] ?? null;
            $payload = json_decode((string) $payloadRaw, true);

            $hasSuccessMessage = str_contains($payload['content'] ?? '', 'Backup Completed Successfully');
            $hasEmbed = ! empty($payload['embeds'][0]['title']) && str_contains($payload['embeds'][0]['title'], 'Database & Uploads Backup');

            // Verify zip attachment
            $document = $data->firstWhere('name', 'files[0]');
            $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
            file_put_contents($tempZip, $document['contents']);

            $zip = new ZipArchive;
            $zip->open($tempZip);

            $hasNews = $zip->locateName('uploads/news/test_news.txt') !== false;
            $hasAr = $zip->locateName('uploads/ar/test_asset.txt') !== false;

            $zip->close();
            @unlink($tempZip);

            return $isDiscordUrl && $hasSuccessMessage && $hasEmbed && $hasNews && ! $hasAr;
        });
    } finally {
        @unlink($arTestFile);
        @unlink($normalTestFile);
    }
});

it('works via backup:run and backup:telegram aliases', function () {
    Http::fake([
        'https://discord.com/api/webhooks/*' => Http::response(['id' => '123'], 200),
    ]);

    $this->artisan('backup:run')
        ->assertExitCode(Command::SUCCESS);

    $this->artisan('backup:telegram')
        ->assertExitCode(Command::SUCCESS);
});

it('skips individual files that exceed maximum file size limit', function () {
    Http::fake([
        'https://discord.com/api/webhooks/*' => Http::response(['id' => '123'], 200),
    ]);

    Config::set('services.discord_backup.max_file_size', 100); // 100 bytes limit

    $testPublicPath = storage_path('app/public');
    $smallFile = "{$testPublicPath}/news/small.txt";
    $largeFile = "{$testPublicPath}/news/large.txt";

    File::ensureDirectoryExists(dirname($smallFile));
    File::put($smallFile, 'Small content under 100 bytes');
    File::put($largeFile, str_repeat('A', 500)); // 500 bytes (exceeds limit)

    try {
        $this->artisan('backup:discord')
            ->assertExitCode(Command::SUCCESS);

        Http::assertSent(function ($request) {
            $data = collect($request->data());
            $document = $data->firstWhere('name', 'files[0]');
            $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
            file_put_contents($tempZip, $document['contents']);

            $zip = new ZipArchive;
            $zip->open($tempZip);

            $hasSmall = $zip->locateName('uploads/news/small.txt') !== false;
            $hasLarge = $zip->locateName('uploads/news/large.txt') !== false;

            $zip->close();
            @unlink($tempZip);

            return $hasSmall && ! $hasLarge;
        });
    } finally {
        @unlink($smallFile);
        @unlink($largeFile);
    }
});

it('dispatches discord notification when discord api returns error', function () {
    Http::fake([
        'https://discord.com/api/webhooks/test/token' => Http::sequence()
            ->push(['message' => 'Invalid Webhook'], 400) // Initial upload attempt fails
            ->push(['id' => '456'], 200), // Failure notification succeeds
    ]);

    $this->artisan('backup:discord')
        ->expectsOutput('Creating zip...')
        ->expectsOutputToContain('Sending backup (')
        ->assertExitCode(Command::FAILURE);

    Http::assertSent(function ($request) {
        if ($request->isMultipart()) {
            return false;
        }

        $content = $request['content'] ?? '';

        return str_contains($content, 'Backup Failed (exponit.com)')
            && str_contains($content, 'Discord API error');
    });
});

it('dispatches discord notification when discord webhook is missing', function () {
    Config::set('services.discord_backup.webhook', null);
    Config::set('discord-alerts.webhook_urls.default', null);

    DiscordAlert::shouldReceive('message')
        ->once()
        ->withArgs(function (string $text) {
            return str_contains($text, 'Backup Failed (exponit.com)')
                && str_contains($text, 'not configured');
        });

    $this->artisan('backup:discord')
        ->assertExitCode(Command::FAILURE);
});
