<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'marker_image_path', 'video_path', 'mind_file_path', 'tracking_score', 'status', 'play_mode'])]
class ArCreative extends Model
{
    /**
     * tracking_score is a 0–100 quality computed in the browser at compile time
     * (image contrast + feature coverage + feature count). Below this, the marker
     * is treated as untrackable and cannot be published.
     */
    public const MIN_TRACKABLE_SCORE = 45;

    protected function casts(): array
    {
        return [
            'tracking_score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Auto-assign a unique slug from the name if one isn't set.
        static::creating(function (ArCreative $creative): void {
            if (blank($creative->slug)) {
                $creative->slug = static::uniqueSlug($creative->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        // Readable prefix from the name (if any) + an unguessable random token,
        // e.g. "calpol-box-reveal-k4p9x2", so AR URLs can't be guessed from the
        // product name. The loop guarantees uniqueness.
        $prefix = Str::slug($name);
        $prefix = $prefix !== '' ? $prefix.'-' : '';

        do {
            $slug = $prefix.Str::lower(Str::random(6));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * The public AR page a QR code points to.
     */
    public function arUrl(): string
    {
        return route('ar.show', $this->slug);
    }

    public function markerImageUrl(): ?string
    {
        return $this->marker_image_path ? asset('storage/'.$this->marker_image_path) : null;
    }

    public function videoUrl(): ?string
    {
        return $this->video_path ? asset('storage/'.$this->video_path) : null;
    }

    public function mindFileUrl(): ?string
    {
        return $this->mind_file_path ? asset('storage/'.$this->mind_file_path) : null;
    }

    /**
     * The marker image's height ÷ width. The AR viewer sizes the video plane to
     * this so the video lands flat on the (horizontal or vertical) marker.
     */
    public function markerAspectRatio(): float
    {
        if (! $this->marker_image_path) {
            return 1.0;
        }

        $path = Storage::disk('public')->path($this->marker_image_path);

        if (! is_file($path) || ! ($size = @getimagesize($path)) || empty($size[0])) {
            return 1.0;
        }

        return round($size[1] / $size[0], 4); // height / width
    }

    /**
     * Whether this creative has everything it needs to run on a phone.
     */
    public function isReady(): bool
    {
        return filled($this->marker_image_path)
            && filled($this->video_path)
            && filled($this->mind_file_path);
    }

    /**
     * Whether the marker image has enough trackable detail to actually work.
     */
    public function isTrackable(): bool
    {
        return $this->tracking_score !== null
            && $this->tracking_score >= self::MIN_TRACKABLE_SCORE;
    }

    /**
     * Quality tier from the stored score: null | poor | fair | good.
     */
    public function trackabilityTier(): ?string
    {
        if ($this->tracking_score === null) {
            return null;
        }

        return match (true) {
            $this->tracking_score >= 70 => 'good',
            $this->tracking_score >= self::MIN_TRACKABLE_SCORE => 'fair',
            default => 'poor',
        };
    }

    public function trackabilityLabel(): string
    {
        return match ($this->trackabilityTier()) {
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor — won’t track',
            default => 'Not compiled',
        };
    }

    public function trackabilityColor(): string
    {
        return match ($this->trackabilityTier()) {
            'good' => 'success',
            'fair' => 'warning',
            'poor' => 'danger',
            default => 'gray',
        };
    }
}
