<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Area $area): void {
            if (blank($area->slug)) {
                $area->slug = static::uniqueSlug($area->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'area';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function headquarter(): BelongsTo
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
