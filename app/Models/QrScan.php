<?php

namespace App\Models;

use Database\Factories\QrScanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    /** @use HasFactory<QrScanFactory> */
    use HasFactory;

    protected $fillable = [
        'qr_code_id',
        'ip_address',
        'ip_hash',
        'country_code',
        'country_name',
        'region',
        'city',
        'latitude',
        'longitude',
        'device_type',
        'device_brand',
        'device_model',
        'platform',
        'platform_version',
        'browser',
        'browser_version',
        'is_unique',
        'referrer',
        'user_agent',
        'scanned_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_unique' => 'boolean',
            'scanned_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * @return BelongsTo<QrCode, $this>
     */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
