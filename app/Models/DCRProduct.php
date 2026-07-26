<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DCRProduct extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
    ];

    protected $table = 'dcr_products';

    public function dcr()
    {
        return $this->belongsTo(DCR::class, 'dcr_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
