<?php

namespace App\Support;

/**
 * Pharma trade-pricing maths (PTR / PTS / scheme / GST / margin).
 *
 * Single source of truth for the *stored* calculation. Mirrors the client-side
 * Alpine version on the public /pricing-calculator page exactly. PTR and PTS are
 * treated as GST-INCLUSIVE (both derive from MRP, which is GST-inclusive by law).
 */
class PricingCalculator
{
    /**
     * @param  array{
     *   mrp: float|int|null, retailer_margin_percent: float|int|null,
     *   stockist_margin_percent: float|int|null, scheme_paid_units: int|null,
     *   scheme_free_units: int|null, gst_percent: float|int|null,
     *   supply_type?: string, unit_cost?: float|int|null
     * }  $in
     * @return array<string, float|null>
     */
    public static function compute(array $in): array
    {
        $mrp = (float) ($in['mrp'] ?? 0);
        $rm = (float) ($in['retailer_margin_percent'] ?? 0);
        $sm = (float) ($in['stockist_margin_percent'] ?? 0);
        $paid = (int) ($in['scheme_paid_units'] ?? 0);
        $free = (int) ($in['scheme_free_units'] ?? 0);
        $gst = (float) ($in['gst_percent'] ?? 0);
        $cost = isset($in['unit_cost']) && $in['unit_cost'] !== null && (float) $in['unit_cost'] > 0
            ? (float) $in['unit_cost']
            : null;

        $ptr = $mrp * (1 - $rm / 100);
        $pts = $ptr * (1 - $sm / 100);

        $total = $paid + $free;
        $effPts = $total > 0 ? ($paid * $pts) / $total : $pts;

        $stockistMarginPerUnit = $ptr - $effPts;
        $stockistMarginPctActual = $ptr != 0.0 ? ($stockistMarginPerUnit / $ptr) * 100 : 0.0;
        $retailerMarginPerUnit = $mrp - $ptr;

        $taxablePts = $gst > -100 ? $pts / (1 + $gst / 100) : $pts;
        $gstAmtPts = $pts - $taxablePts;

        $invoiceTaxable = $paid * $taxablePts;
        $invoiceGst = $paid * $gstAmtPts;
        $invoiceTotal = $invoiceTaxable + $invoiceGst;

        $profit = $profitMarginPct = $markupPct = $ratio = null;
        if ($cost !== null) {
            $profit = $effPts - $cost;
            $profitMarginPct = $effPts != 0.0 ? ($profit / $effPts) * 100 : 0.0;
            $markupPct = ($profit / $cost) * 100;
            $ratio = $mrp / $cost;
        }

        return [
            'mrp' => $mrp,
            'ptr' => $ptr,
            'pts' => $pts,
            'effective_pts' => $effPts,
            'retailer_margin_per_unit' => $retailerMarginPerUnit,
            'stockist_margin_per_unit' => $stockistMarginPerUnit,
            'stockist_margin_percent_actual' => $stockistMarginPctActual,
            'taxable_value_pts' => $taxablePts,
            'gst_amount_pts' => $gstAmtPts,
            'invoice_taxable_value' => $invoiceTaxable,
            'invoice_gst_amount' => $invoiceGst,
            'invoice_total' => $invoiceTotal,
            'profit_per_unit' => $profit,
            'profit_margin_percent' => $profitMarginPct,
            'markup_percent' => $markupPct,
            'mrp_to_cost_ratio' => $ratio,
        ];
    }
}
