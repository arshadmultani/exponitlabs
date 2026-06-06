<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Showcase extends Model
{
    use HasFactory;

    public const DISK = 'public';

    /** Max showcases allowed per doctor (replaces sma's MicrositeSettings). */
    public const MAX_PER_DOCTOR = 20;

    /** Upload size limits in KB. */
    public const MAX_IMAGE_KB = 4096;   // 4 MB

    public const MAX_VIDEO_KB = 51200;  // 50 MB

    protected $fillable = [
        'doctor_id',
        'title',
        'description',
        'media_type',
        'media_url',
    ];

    protected $appends = ['media_file_url'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getMediaFileUrlAttribute(): ?string
    {
        return $this->media_url
            ? Storage::disk(self::DISK)->url($this->media_url)
            : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (Showcase $showcase) {
            if ($showcase->media_url && Storage::disk(self::DISK)->exists($showcase->media_url)) {
                Storage::disk(self::DISK)->delete($showcase->media_url);
            }
        });
    }
}
