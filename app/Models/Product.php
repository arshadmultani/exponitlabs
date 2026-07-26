<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const DISK = 'public';

    protected $fillable = [
        'therapeutic_area_id',
        'name',
        'slug',
        'category',
        'composition',
        'strength',
        'packaging',
        'mrp',
        'unit_cost',
        'description',
        'image_path',
        'images',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'images' => 'array',
            'sort_order' => 'integer',
            'mrp' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });

        static::deleting(function (Product $product): void {
            $paths = array_merge(
                $product->image_path ? [$product->image_path] : [],
                $product->images ?? [],
            );

            foreach (array_unique($paths) as $path) {
                if ($path && Storage::disk(self::DISK)->exists($path)) {
                    Storage::disk(self::DISK)->delete($path);
                }
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
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

    public function therapeuticArea(): BelongsTo
    {
        return $this->belongsTo(TherapeuticArea::class);
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk(self::DISK)->url($this->image_path) : null;
    }

    /**
     * Full gallery as public URLs: cover image first, then the extra images,
     * de-duplicated. Used by the product page lightbox/thumbnail strip.
     *
     * @return array<int, string>
     */
    public function galleryUrls(): array
    {
        $paths = array_merge(
            $this->image_path ? [$this->image_path] : [],
            $this->images ?? [],
        );

        return collect($paths)
            ->filter()
            ->unique()
            ->map(fn (string $path) => Storage::disk(self::DISK)->url($path))
            ->values()
            ->all();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function dcrProducts()
    {
        return $this->hasMany(DCRProduct::class);
    }
}
