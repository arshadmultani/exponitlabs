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
        'uuid',
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
        'visit_cost_override',
        'doctor_potential_id',
        'visiting_days',
        'visiting_shifts',
    ];

    protected $casts = [
        'practice_since' => 'date',
        'location' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'visit_cost_override' => 'decimal:2',
        'visiting_days' => 'array',
        'visiting_shifts' => 'array',
    ];

    /**
     * Keep the numeric lat/lng columns (used for proximity / tour-planning
     * queries) in sync with the GeoJSON point edited on the map.
     */
    protected static function booted(): void
    {
        static::creating(function (Doctor $doctor): void {
            if (empty($doctor->doctor_potential_id)) {
                $doctor->doctor_potential_id = DoctorPotential::where('code', 'nascent')->value('id');
            }
        });

        static::saving(function (Doctor $doctor): void {
            $coords = data_get($doctor->location, 'features.0.geometry.coordinates');

            if (is_array($coords) && count($coords) >= 2) {
                [$doctor->longitude, $doctor->latitude] = [$coords[0], $coords[1]];
            }

            if (is_array($doctor->visiting_days) && empty($doctor->visiting_days)) {
                $doctor->visiting_days = null;
            }

            if (is_array($doctor->visiting_shifts) && empty($doctor->visiting_shifts)) {
                $doctor->visiting_shifts = null;
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

    public function headquarter(): BelongsTo
    {
        return $this->belongsTo(Headquarter::class);
    }

    protected $appends = ['profile_photo_url', 'whatsapp_number'];

    public function getWhatsappNumberAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);
        if (str_starts_with($digits, '0091')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '091')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        } elseif (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        return $digits;
    }

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

    public function dcrs(): HasMany
    {
        return $this->hasMany(DCR::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(DoctorRevenue::class);
    }

    public function potential(): BelongsTo
    {
        return $this->belongsTo(DoctorPotential::class, 'doctor_potential_id');
    }

    public function scopeAvailableOnDay(Builder $query, string $day): Builder
    {
        return $query->where(function (Builder $q) use ($day) {
            $q->whereJsonContains('visiting_days', $day)
                ->orWhereNull('visiting_days')
                ->orWhere('visiting_days', '[]')
                ->orWhere('visiting_days', '');
        });
    }

    public function scopeAvailableOnShift(Builder $query, string $shift): Builder
    {
        return $query->where(function (Builder $q) use ($shift) {
            $q->whereJsonContains('visiting_shifts', $shift)
                ->orWhereNull('visiting_shifts')
                ->orWhere('visiting_shifts', '[]')
                ->orWhere('visiting_shifts', '');
        });
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
