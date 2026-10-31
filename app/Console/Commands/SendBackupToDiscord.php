<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Throwable;
use ZipArchive;

#[Signature('backup:discord')]
#[Description('Zip database and uploads, send to Discord')]
class SendBackupToDiscord extends Command
{
    /**
     * The console command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['backup:run', 'backup:telegram'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $webhook = config('services.discord_backup.webhook')
            ?: config('discord-alerts.webhook_urls.default');

        if (blank($webhook)) {
            $this->handleFailure('Discord backup webhook URL is not configured.');

            return self::FAILURE;
        }

        $date = now()->format('Y-m-d_H-i');
        $zipPath = storage_path("app/backup-{$date}.zip");

        try {
            $this->info('Creating zip...');

            $zip = new ZipArchive;
            $status = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($status !== true) {
                $this->handleFailure("Failed to create zip archive at {$zipPath} (ZipArchive error code: {$status}).", $webhook);

                return self::FAILURE;
            }

            // Add SQLite database
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
            }

            // Add uploaded files, excluding large assets
            $uploadsPath = storage_path('app/public');
            $excludedDirectories = (array) config('services.discord_backup.excluded_directories', ['ar']);
            $maxFileSizeBytes = (int) config('services.discord_backup.max_file_size', 15 * 1024 * 1024);

            if (is_dir($uploadsPath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($uploadsPath, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($files as $file) {
                    if (! $file->isDir()) {
                        $relativePath = substr($file->getPathname(), strlen($uploadsPath) + 1);
                        $normalizedPath = str_replace('\\', '/', $relativePath);
                        $topDir = explode('/', $normalizedPath)[0] ?? '';

                        // Skip excluded asset directories (e.g. 'ar')
                        if (in_array($topDir, $excludedDirectories, true)) {
                            continue;
                        }

                        // Skip individual files exceeding max file size
                        if ($maxFileSizeBytes > 0 && $file->getSize() > $maxFileSizeBytes) {
                            continue;
                        }

                        $zip->addFile($file->getPathname(), 'uploads/'.$normalizedPath);
                    }
                }
            }

            $zip->close();

            $fileSizeBytes = file_exists($zipPath) ? (int) filesize($zipPath) : 0;
            $fileSizeFormatted = $this->formatBytes($fileSizeBytes);

            // Discord max upload limit for standard webhooks is 25 MB
            if ($fileSizeBytes > 25 * 1024 * 1024) {
                $this->handleFailure("Backup archive ({$fileSizeFormatted}) exceeds Discord's 25 MB webhook limit. Exclude additional directories in BACKUP_EXCLUDE_DIRS.", $webhook);

                return self::FAILURE;
            }

            $this->info("Sending backup ({$fileSizeFormatted}) to Discord...");

            $payload = [
                'content' => '✅ **Backup Completed Successfully**',
                'embeds' => [
                    [
                        'title' => '🗄️ Database & Uploads Backup',
                        'color' => 0x22C55E, // emerald-500
                        'fields' => [
                            [
                                'name' => 'Archive Size',
                                'value' => $fileSizeFormatted,
                                'inline' => true,
                            ],
                            [
                                'name' => 'Timestamp',
                                'value' => now()->format('d M Y, H:i:s'),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Contents',
                                'value' => 'SQLite Database (`database.sqlite`) + Public Uploads (excluding `ar/`)',
                                'inline' => false,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::timeout(180)->attach(
                'files[0]',
                file_get_contents($zipPath),
                "backup-{$date}.zip"
            )->post($webhook, [
                'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
            ]);

            if ($response->successful()) {
                $this->info('Backup sent to Discord successfully.');

                return self::SUCCESS;
            }

            $this->handleFailure("Discord API error (status {$response->status()}): {$response->body()}", $webhook);

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->handleFailure('Exception during backup: '.$e->getMessage(), $webhook ?? null);

            return self::FAILURE;
        } finally {
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
        }
    }

    /**
     * Report backup failure to console and notify via Discord alert.
     */
    protected function handleFailure(string $reason, ?string $webhookUrl = null): void
    {
        $this->error('Failed: '.$reason);

        $message = implode("\n", [
            '🚨 **Backup Failed (exponit.com)**',
            "**Reason:** {$reason}",
            '**Time:** '.now()->format('d M Y, H:i:s'),
        ]);

        try {
            if ($webhookUrl) {
                Http::timeout(15)->post($webhookUrl, [
                    'content' => $message,
                ]);
            } else {
                DiscordAlert::message($message);
            }
        } catch (Throwable $e) {
            Log::warning('Discord backup failure alert failed: '.$e->getMessage());
        }
    }

    /**
     * Format bytes into a human-readable size string.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min((int) $pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
