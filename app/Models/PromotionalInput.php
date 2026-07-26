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
        'is_active',
    ];

    public function dcrPromotionalItems()
    {
        return $this->hasMany(DCRPromotionalInput::class, 'promotional_input_id');
    }
}
