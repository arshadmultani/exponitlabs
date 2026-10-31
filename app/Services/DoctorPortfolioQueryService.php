<?php

namespace App\Services;

use App\Models\DCR;
use App\Models\Doctor;
use App\Models\DoctorRevenue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorPortfolioQueryService
{
    /**
     * Build the base portfolio query with correlated subqueries wrapped in an outer fromSub projection.
     */
    public function getBaseQuery(?Carbon $startDate = null, ?Carbon $endDate = null): Builder
    {
        $defaultVisitCost = (float) config('exponit.default_visit_cost', 250.00);

        // Snap revenue start date to beginning of month to prevent rolling date exclusion
        $revenueStartDate = $startDate ? $startDate->copy()->startOfMonth() : null;

        $visitsQuery = DCR::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $samplesQuery = DB::table('dcr_products')
            ->join('d_c_r_s', 'd_c_r_s.id', '=', 'dcr_products.dcr_id')
            ->join('products', 'products.id', '=', 'dcr_products.product_id')
            ->selectRaw('COALESCE(SUM(dcr_products.quantity * COALESCE(dcr_products.unit_cost_snapshot, products.sample_cost, products.unit_cost, 0.00)), 0)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $promosQuery = DB::table('dcr_promotional_inputs')
            ->join('d_c_r_s', 'd_c_r_s.id', '=', 'dcr_promotional_inputs.dcr_id')
            ->join('promotional_inputs', 'promotional_inputs.id', '=', 'dcr_promotional_inputs.promotional_input_id')
            ->selectRaw('COALESCE(SUM(dcr_promotional_inputs.quantity * COALESCE(dcr_promotional_inputs.unit_cost_snapshot, promotional_inputs.unit_cost, 0.00)), 0)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->when($startDate, fn ($q) => $q->where('d_c_r_s.date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('d_c_r_s.date', '<=', $endDate));

        $revenueQuery = DoctorRevenue::query()
            ->selectRaw('COALESCE(SUM(doctor_revenues.amount), 0)')
            ->whereColumn('doctor_revenues.doctor_id', 'doctors.id')
            ->when($revenueStartDate, fn ($q) => $q->where('doctor_revenues.period_date', '>=', $revenueStartDate))
            ->when($endDate, fn ($q) => $q->where('doctor_revenues.period_date', '<=', $endDate));

        $innerQuery = Doctor::query()
            ->select('doctors.*')
            ->selectSub($visitsQuery, 'visits_count')
            ->selectSub($samplesQuery, 'samples_cost')
            ->selectSub($promosQuery, 'promos_cost')
            ->selectSub($revenueQuery, 'total_revenue');

        return Doctor::query()
            ->fromSub($innerQuery, 'doctors')
            ->select('doctors.*')
            ->selectRaw('((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost) AS total_investment', [$defaultVisitCost])
            ->selectRaw('(doctors.total_revenue - ((doctors.visits_count * COALESCE(doctors.visit_cost_override, ?)) + doctors.samples_cost + doctors.promos_cost)) AS net_pnl', [$defaultVisitCost]);
    }

    /**
     * Resolve start and end Carbon timestamps for predefined accounting horizons.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public function resolveHorizonRange(string $horizon): array
    {
        $now = Carbon::now();

        return match ($horizon) {
            'current_fy' => [
                $now->month >= 4
                    ? Carbon::create($now->year, 4, 1)->startOfDay()
                    : Carbon::create($now->year - 1, 4, 1)->startOfDay(),
                $now->month >= 4
                    ? Carbon::create($now->year + 1, 3, 31)->endOfDay()
                    : Carbon::create($now->year, 3, 31)->endOfDay(),
            ],
            'last_3_months' => [
                $now->copy()->subMonths(3)->startOfMonth(),
                $now->endOfDay(),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            default => [null, null], // all_time
        };
    }

    /**
     * Calculate aggregated portfolio totals for the executive summary stats widget.
     *
     * @return array{
     *     total_investment: float,
     *     total_revenue: float,
     *     net_pnl: float,
     *     return_multiple: float
     * }
     */
    public function calculatePortfolioStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = $this->getBaseQuery($startDate, $endDate);

        $results = DB::table($query, 'portfolio_agg')
            ->selectRaw('COALESCE(SUM(total_investment), 0) as aggregate_investment')
            ->selectRaw('COALESCE(SUM(total_revenue), 0) as aggregate_revenue')
            ->selectRaw('COALESCE(SUM(net_pnl), 0) as aggregate_net_pnl')
            ->first();

        $totalInvestment = (float) ($results?->aggregate_investment ?? 0.0);
        $totalRevenue = (float) ($results?->aggregate_revenue ?? 0.0);
        $netPnl = (float) ($results?->aggregate_net_pnl ?? 0.0);

        $returnMultiple = $this->calculateReturnMultiple($totalInvestment, $totalRevenue);

        return [
            'total_investment' => $totalInvestment,
            'total_revenue' => $totalRevenue,
            'net_pnl' => $netPnl,
            'return_multiple' => $returnMultiple,
        ];
    }

    /**
     * Compute return multiple with zero-division safeguard.
     */
    public function calculateReturnMultiple(float $totalInvestment, float $totalRevenue): float
    {
        if ($totalInvestment <= 0.0) {
            return 0.0;
        }

        return round($totalRevenue / $totalInvestment, 2);
    }

    /**
     * Calculate ROI percentage with zero-division guard.
     */
    public function calculateRoiPercentage(float $totalInvestment, float $netPnl): ?float
    {
        if ($totalInvestment <= 0.0) {
            return null; // Organic return without detailing investment
        }

        return round(($netPnl / $totalInvestment) * 100, 2);
    }

    /**
     * Determine deterministic performance tier for a doctor row.
     */
    public function resolveTier(object $doctor): string
    {
        // 1. Incubation: Registered in the last 60 days
        $createdAt = isset($doctor->created_at) ? Carbon::parse($doctor->created_at) : null;
        if ($createdAt && $createdAt->greaterThanOrEqualTo(now()->subDays(60))) {
            return 'incubation';
        }

        $netPnl = (float) ($doctor->net_pnl ?? 0.0);
        $totalInvestment = (float) ($doctor->total_investment ?? 0.0);

        // 4. Under-yielding: Net P&L < 0
        if ($netPnl < 0.0) {
            return 'under_yielding';
        }

        $roi = $this->calculateRoiPercentage($totalInvestment, $netPnl);

        // 2. High-Yield: Net P&L > 0 and ROI >= 50% (or organic return where investment is 0 and P&L > 0)
        if ($netPnl > 0.0 && ($roi === null || $roi >= 50.0)) {
            return 'high_yield';
        }

        // 3. Balanced: Net P&L >= 0 and ROI < 50%
        return 'balanced';
    }
}
