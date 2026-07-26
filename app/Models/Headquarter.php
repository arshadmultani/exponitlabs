<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Headquarter extends Model
{
    /** @use HasFactory<HeadquarterFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}
