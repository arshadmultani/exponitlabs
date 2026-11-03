<?php

namespace App\Models;

use Database\Factories\FestivalDateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FestivalDate extends Model
{
    /** @use HasFactory<FestivalDateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'festival_id',
        'year',
        'date',
        'is_manually_adjusted',
        'adjusted_by_user_id',
        'adjustment_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'date' => 'date',
            'is_manually_adjusted' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Festival, $this>
     */
    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by_user_id');
    }
}
