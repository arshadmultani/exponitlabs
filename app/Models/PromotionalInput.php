<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalInput extends Model
{
    /** @use HasFactory<PromotionalInputFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_cost',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function dcrPromotionalItems()
    {
        return $this->hasMany(DCRPromotionalInput::class, 'promotional_input_id');
    }
}
