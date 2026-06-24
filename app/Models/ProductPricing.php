<?php

namespace App\Models;

use App\Support\PricingCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'label',
        'retailer_margin_percent',
        'stockist_margin_percent',
        'scheme_paid_units',
        'scheme_free_units',
        'gst_percent',
        'supply_type',
        'unit_cost',
        // Computed columns are filled by the saving hook, not user input.
    ];

    protected function casts(): array
    {
        return [
            'retailer_margin_percent' => 'decimal:2',
            'stockist_margin_percent' => 'decimal:2',
            'scheme_paid_units' => 'integer',
            'scheme_free_units' => 'integer',
            'gst_percent' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'mrp_snapshot' => 'decimal:2',
            'ptr' => 'decimal:4',
            'pts' => 'decimal:4',
            'effective_pts' => 'decimal:4',
            'retailer_margin_per_unit' => 'decimal:4',
            'stockist_margin_per_unit' => 'decimal:4',
            'stockist_margin_percent_actual' => 'decimal:2',
            'taxable_value_pts' => 'decimal:4',
            'gst_amount_pts' => 'decimal:4',
            'invoice_total' => 'decimal:4',
            'profit_per_unit' => 'decimal:4',
            'profit_margin_percent' => 'decimal:2',
            'markup_percent' => 'decimal:2',
            'mrp_to_cost_ratio' => 'decimal:2',
        ];
    }

    /**
     * Recompute and store the snapshot whenever inputs change. MRP is taken from
     * the parent product so stored figures always match the calculator output.
     */
    protected static function booted(): void
    {
        static::saving(function (ProductPricing $pricing): void {
            $mrp = $pricing->product?->mrp ?? $pricing->mrp_snapshot ?? 0;

            $r = PricingCalculator::compute([
                'mrp' => $mrp,
                'retailer_margin_percent' => $pricing->retailer_margin_percent,
                'stockist_margin_percent' => $pricing->stockist_margin_percent,
                'scheme_paid_units' => $pricing->scheme_paid_units,
                'scheme_free_units' => $pricing->scheme_free_units,
                'gst_percent' => $pricing->gst_percent,
                'supply_type' => $pricing->supply_type,
                'unit_cost' => $pricing->unit_cost,
            ]);

            $pricing->mrp_snapshot = $mrp;
            $pricing->ptr = $r['ptr'];
            $pricing->pts = $r['pts'];
            $pricing->effective_pts = $r['effective_pts'];
            $pricing->retailer_margin_per_unit = $r['retailer_margin_per_unit'];
            $pricing->stockist_margin_per_unit = $r['stockist_margin_per_unit'];
            $pricing->stockist_margin_percent_actual = $r['stockist_margin_percent_actual'];
            $pricing->taxable_value_pts = $r['taxable_value_pts'];
            $pricing->gst_amount_pts = $r['gst_amount_pts'];
            $pricing->invoice_total = $r['invoice_total'];
            $pricing->profit_per_unit = $r['profit_per_unit'];
            $pricing->profit_margin_percent = $r['profit_margin_percent'];
            $pricing->markup_percent = $r['markup_percent'];
            $pricing->mrp_to_cost_ratio = $r['mrp_to_cost_ratio'];
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
