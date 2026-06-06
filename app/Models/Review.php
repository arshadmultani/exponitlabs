<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory;

    public const DISK = 'public';

    /** Max reviews allowed per doctor (replaces sma's MicrositeSettings). */
    public const MAX_PER_DOCTOR = 20;

    /** Upload size limits in KB. */
    public const MAX_IMAGE_KB = 4096;   // 4 MB

    public const MAX_VIDEO_KB = 20480;  // 20 MB

    protected $fillable = [
        'doctor_id',
        'reviewer_name',
        'submitted_by_name',
        'rating',
        'review_text',
        'media_url',
        'media_type',
        'status',
        'verified_at',
    ];

    protected $appends = ['media_file_url'];

    protected $casts = [
        'rating' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function verified(): bool
    {
        return filled($this->verified_at);
    }

    public function getMediaFileUrlAttribute(): ?string
    {
        return $this->media_url
            ? Storage::disk(self::DISK)->url($this->media_url)
            : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (Review $review) {
            if ($review->media_url && Storage::disk(self::DISK)->exists($review->media_url)) {
                Storage::disk(self::DISK)->delete($review->media_url);
            }
        });
    }
}
