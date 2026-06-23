<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'area_id',
        'address',
        'clinic_name',
        'latitude',
        'longitude',
        'location',
        'practice_since',
        'status',
    ];

    protected $casts = [
        'practice_since' => 'date',
        'location' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Keep the numeric lat/lng columns (used for proximity / tour-planning
     * queries) in sync with the GeoJSON point edited on the map.
     */
    protected static function booted(): void
    {
        static::saving(function (Doctor $doctor): void {
            $coords = data_get($doctor->location, 'features.0.geometry.coordinates');

            if (is_array($coords) && count($coords) >= 2) {
                [$doctor->longitude, $doctor->latitude] = [$coords[0], $coords[1]];
            }
        });
    }

    /**
     * Build a GeoJSON FeatureCollection (single Point) from lat/lng.
     */
    public static function pointGeoJson(float $latitude, float $longitude): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => (object) [],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$longitude, $latitude],
                ],
            ]],
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

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
