<?php

use App\Models\Product;
use App\Models\ProductPricing;

it('stores the computed snapshot from the product MRP on save', function () {
    $product = Product::factory()->create(['mrp' => 120]);

    $pricing = ProductPricing::create([
        'product_id' => $product->id,
        'label' => '10+3 scheme',
        'retailer_margin_percent' => 20,
        'stockist_margin_percent' => 10,
        'scheme_paid_units' => 10,
        'scheme_free_units' => 3,
        'gst_percent' => 5,
        'supply_type' => 'intra_state',
        'unit_cost' => 8,
    ]);

    expect((float) $pricing->mrp_snapshot)->toBe(120.0);
    expect(round((float) $pricing->ptr, 2))->toBe(91.43);
    expect(round((float) $pricing->pts, 2))->toBe(82.29);
    expect(round((float) $pricing->effective_pts, 2))->toBe(63.30);
    expect(round((float) $pricing->profit_per_unit, 2))->toBe(55.30);
    expect(round((float) $pricing->mrp_to_cost_ratio, 1))->toBe(15.0);
});

it('recomputes the snapshot when inputs change', function () {
    $product = Product::factory()->create(['mrp' => 200]);
    $pricing = ProductPricing::create([
        'product_id' => $product->id,
        'retailer_margin_percent' => 20,
        'stockist_margin_percent' => 10,
        'scheme_paid_units' => 10,
        'scheme_free_units' => 0,
        'gst_percent' => 5,
        'supply_type' => 'intra_state',
    ]);

    expect(round((float) $pricing->ptr, 2))->toBe(152.38); // 200/1.05 * 0.8

    $pricing->update(['retailer_margin_percent' => 25]);
    expect(round((float) $pricing->ptr, 2))->toBe(142.86); // 200/1.05 * 0.75
});
