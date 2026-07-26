<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DCRPromotionalInput extends Model
{
    protected $fillable = [
        'promotional_input_id',
        'quantity',
    ];

    protected $table = 'dcr_promotional_inputs';

    public function dcr()
    {
        return $this->belongsTo(DCR::class, 'dcr_id');
    }

    public function promotionalInput()
    {
        return $this->belongsTo(PromotionalInput::class, 'promotional_input_id');
    }
}
