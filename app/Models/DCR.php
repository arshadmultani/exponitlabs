<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DCR extends Model
{
    /** @use HasFactory<DCRFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'date',
        'doctor_id',
        'remarks',
        'reminder_sent_at',
    ];

    protected $casts = [
        'date' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DCR $dcr): void {
            $dcr->uuid = $dcr->uuid ?? (string) Str::uuid();
        });
    }

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
