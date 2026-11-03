<?php

namespace App\Services;

use App\Models\DCR;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DoctorCadenceQueryService
{
    /**
     * Build the query for doctor cadence and route planning with attached scalar subqueries.
     */
    public function getEloquentQuery(?Carbon $referenceDate = null): Builder
    {
        $date = $referenceDate ?? Carbon::today();
        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();

        $totalVisitsQuery = DCR::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id');

        $visitsThisMonthQuery = DCR::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id')
            ->where('d_c_r_s.date', '>=', $startOfMonth)
            ->where('d_c_r_s.date', '<=', $endOfMonth);

        $lastVisitDateQuery = DCR::query()
            ->selectRaw('date(MAX(d_c_r_s.date))')
            ->whereColumn('d_c_r_s.doctor_id', 'doctors.id');

        return Doctor::query()
            ->where('doctors.status', 'active')
            ->select('doctors.*')
            ->selectSub($totalVisitsQuery, 'total_visits_count')
            ->selectSub($visitsThisMonthQuery, 'visits_this_month_count')
            ->selectSub($lastVisitDateQuery, 'last_visit_date')
            ->with(['potential', 'area.headquarter']);
    }
}
