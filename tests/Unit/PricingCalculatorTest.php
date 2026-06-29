<?php

use App\Support\PricingCalculator;

it('computes PTR, PTS, scheme, GST and profit correctly', function () {
    $r = PricingCalculator::compute([
        'mrp' => 120,
        'retailer_margin_percent' => 20,
        'stockist_margin_percent' => 10,
        'scheme_paid_units' => 10,
        'scheme_free_units' => 3,
        'gst_percent' => 5,
        'supply_type' => 'intra_state',
        'unit_cost' => 8,
    ]);

    // PTR/PTS are GST-EXCLUSIVE: PTR = (MRP − MRP×RM%) / (1 + GST%); PTS = PTR × (1 − SM%).
    expect(round($r['ptr'], 2))->toBe(91.43);
    expect(round($r['pts'], 2))->toBe(82.29);
    expect(round($r['effective_pts'], 2))->toBe(63.30);
    expect(round($r['retailer_margin_per_unit'], 2))->toBe(22.86);
    expect(round($r['stockist_margin_percent_actual'], 1))->toBe(30.8);
    expect(round($r['taxable_value_pts'], 2))->toBe(82.29);
    expect(round($r['gst_amount_pts'], 2))->toBe(4.11);
    // Cross-check: per-unit invoice total = PTS + GST (GST added on top of net PTS).
    expect(round($r['taxable_value_pts'] + $r['gst_amount_pts'], 2))->toBe(86.4);
    expect(round($r['profit_per_unit'], 2))->toBe(55.30);
    expect(round($r['markup_percent'], 1))->toBe(691.2);
    expect($r['mrp_to_cost_ratio'])->toBe(15.0);
});

it('skips profit figures when no unit cost', function () {
    $r = PricingCalculator::compute([
        'mrp' => 100, 'retailer_margin_percent' => 20, 'stockist_margin_percent' => 10,
        'scheme_paid_units' => 1, 'scheme_free_units' => 0, 'gst_percent' => 5,
    ]);

    expect($r['profit_per_unit'])->toBeNull();
    expect($r['mrp_to_cost_ratio'])->toBeNull();
});

it('makes effective PTS equal PTS when there are no free units', function () {
    $r = PricingCalculator::compute([
        'mrp' => 100, 'retailer_margin_percent' => 20, 'stockist_margin_percent' => 10,
        'scheme_paid_units' => 10, 'scheme_free_units' => 0, 'gst_percent' => 5,
    ]);

    expect(round($r['effective_pts'], 4))->toBe(round($r['pts'], 4));
});
