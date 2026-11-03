<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorPotential extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'color',
        'sort_order',
        'target_visits_per_month',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'target_visits_per_month' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Doctor, $this>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
