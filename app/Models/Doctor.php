<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    /**
     * Media lives on the local "public" disk (shared hosting, no S3).
     */
    public const DISK = 'public';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'specialty',
        'qualification',
        'profile_photo',
        'town',
        'address',
        'practice_since',
        'status',
    ];

    protected $casts = [
        'practice_since' => 'date',
    ];

    protected $appends = ['profile_photo_url'];

    public function microsite(): HasOne
    {
        return $this->hasOne(Microsite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function showcases(): HasMany
    {
        return $this->hasMany(Showcase::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Whole years since the doctor began practising (min 1), or null if unset.
     */
    public function experienceYears(): ?int
    {
        return $this->practice_since
            ? max(1, (int) $this->practice_since->diffInYears(now()))
            : null;
    }

    public function getHasProfilePhotoAttribute(): bool
    {
        return filled($this->profile_photo);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? Storage::disk(self::DISK)->url($this->profile_photo)
            : null;
    }
}
