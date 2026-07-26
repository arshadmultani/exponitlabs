<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DCR extends Model
{
    /** @use HasFactory<DCRFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'doctor_id',
        'remarks',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function sampleProducts()
    {
        return $this->hasMany(DCRProduct::class, 'dcr_id');
    }

    public function promotionalInputs()
    {
        return $this->hasMany(DCRPromotionalInput::class, 'dcr_id');
    }
}
