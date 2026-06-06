<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Microsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'url',
        'is_active',
        'design_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'design_settings' => 'array',
    ];

    /**
     * Public URL slug, e.g. /dr/jane-doe-ab12cd.
     */
    public function getRouteKeyName(): string
    {
        return 'url';
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Doctor::class, 'id', 'doctor_id', 'doctor_id', 'id');
    }

    public function showcases(): HasManyThrough
    {
        return $this->hasManyThrough(Showcase::class, Doctor::class, 'id', 'doctor_id', 'doctor_id', 'id');
    }

    public function getBgColorAttribute(): ?string
    {
        return $this->design_settings['bg_color'] ?? null;
    }

    public function publicUrl(): string
    {
        return route('microsite.show', $this);
    }

    /**
     * Readable slug from the doctor's name + a short random token to keep it unique.
     */
    public static function uniqueUrl(string $doctorName): string
    {
        $base = Str::slug($doctorName) ?: 'doctor';

        do {
            $url = $base.'-'.Str::lower(Str::random(6));
        } while (static::where('url', $url)->exists());

        return $url;
    }

    /**
     * When a microsite is removed, take the doctor's reviews and showcases (and
     * their media files) with it.
     */
    protected static function booted(): void
    {
        static::deleting(function (Microsite $microsite) {
            $doctor = $microsite->doctor;

            if (! $doctor) {
                return;
            }

            // Iterate so each model's deleting hook removes its media file too.
            $doctor->showcases->each->delete();
            $doctor->reviews->each->delete();
        });
    }
}
