<?php

namespace App\Models;

use Database\Factories\QrCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'destination_url',
        'ios_url',
        'android_url',
        'fallback_url',
        'is_active',
        'expires_at',
        'max_scans',
        'total_scans',
        'unique_scans',
        'last_scanned_at',
        'design',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'design' => 'array',
            'total_scans' => 'integer',
            'unique_scans' => 'integer',
            'max_scans' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (QrCode $qrCode): void {
            if (empty($qrCode->uuid)) {
                $qrCode->uuid = (string) Str::uuid();
            }

            if (empty($qrCode->code)) {
                $qrCode->code = static::generateUniqueCode();
            }

            if ($qrCode->design === null) {
                $qrCode->design = static::defaultDesign();
            }
        });
    }

    /**
     * Generate an alphanumeric short code.
     */
    public static function generateUniqueCode(int $length = 7): string
    {
        do {
            $code = Str::lower(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Default visual design properties for the QR code.
     *
     * @return array<string, mixed>
     */
    public static function defaultDesign(): array
    {
        return [
            'foreground_color' => '#0f172a',
            'background_color' => '#ffffff',
            'error_correction' => 'high', // low, medium, quartile, high
            'size' => 600,
            'margin' => 16,
            'logo_path' => null,
            'label_text' => null,
        ];
    }

    /**
     * Get the public tracking/redirect URL encoded in the QR.
     */
    public function shortUrl(): string
    {
        return url("/q/{$this->code}");
    }

    /**
     * Determine if this QR code is expired or reached scan threshold.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return true;
        }

        if ($this->max_scans !== null && $this->total_scans >= $this->max_scans) {
            return true;
        }

        return false;
    }

    /**
     * Resolve target URL based on device platform.
     */
    public function getEffectiveDestinationUrl(?string $platform = null): string
    {
        if ($platform === 'iOS' && ! empty($this->ios_url)) {
            return $this->ios_url;
        }

        if ($platform === 'Android' && ! empty($this->android_url)) {
            return $this->android_url;
        }

        return $this->destination_url;
    }

    /**
     * Resolve fallback URL when inactive or expired.
     */
    public function getEffectiveFallbackUrl(): string
    {
        return ! empty($this->fallback_url) ? $this->fallback_url : url('/');
    }

    /**
     * @return HasMany<QrScan, $this>
     */
    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
