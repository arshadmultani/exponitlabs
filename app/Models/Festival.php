<?php

namespace App\Models;

use Database\Factories\FestivalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Festival extends Model
{
    /** @use HasFactory<FestivalFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category',
        'target_specialties',
        'days_before',
        'days_after',
        'greeting_message_template',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_specialties' => 'array',
            'days_before' => 'integer',
            'days_after' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<FestivalDate, $this>
     */
    public function dates(): HasMany
    {
        return $this->hasMany(FestivalDate::class);
    }
}
