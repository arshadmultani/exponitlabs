<?php

namespace App\Models;

use Database\Factories\DoctorRevenueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRevenue extends Model
{
    /** @use HasFactory<DoctorRevenueFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'period_date',
        'amount',
        'source',
        'notes',
        'created_by_user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
