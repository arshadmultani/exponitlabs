<?php

namespace App\Jobs;

use App\Models\QrCode;
use App\Models\QrScan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecordQrScanJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $telemetry
     */
    public function __construct(
        public int $qrCodeId,
        public array $telemetry,
        public bool $isUnique = false,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $qrCode = QrCode::find($this->qrCodeId);

        if (! $qrCode) {
            return;
        }

        $scanData = array_merge($this->telemetry, [
            'qr_code_id' => $qrCode->id,
            'is_unique' => $this->isUnique,
        ]);

        DB::transaction(function () use ($qrCode, $scanData): void {
            QrScan::create($scanData);

            $qrCode->timestamps = false;
            $qrCode->increment('total_scans');

            if ($this->isUnique) {
                $qrCode->increment('unique_scans');
            }

            $qrCode->updateQuietly([
                'last_scanned_at' => now(),
            ]);
        });
    }
}
