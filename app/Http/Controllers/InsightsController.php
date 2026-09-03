<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pharma sales-audit dashboard (/insights). Reads the pre-built, read-only
 * `insights` SQLite connection (see ImportInsights). Pack-level Value data with
 * monthly trend; everything is sliced live with indexed WHERE clauses, which
 * benchmarks in tens of milliseconds even across all ~112k packs.
 *
 * Gating: the routes sit in their own group in web.php — add `->middleware('auth')`
 * there to lock the dashboard down. This is internal competitive intel, not public.
 */
class InsightsController extends Controller
{
    /** Dimension filters: query-param => packs column. */
    private const FILTERS = [
        'supergroup' => 'supergroup',
        'group' => 'group_desc',
        'molecule' => 'molecule_desc',
        'company' => 'company',
        'brand' => 'brands',
        'acute_chronic' => 'acute_chronic',
        'indian_mnc' => 'indian_mnc',
    ];

    private function db()
    {
        return DB::connection('insights');
    }

    public function index()
    {
        // Lightweight option lists for the dropdowns. Molecule/brand/company are
        // too numerous to preload — those use the free-text search box instead.
        $db = $this->db();
        $meta = $db->table('meta')->pluck('value', 'key');

        // Dimensions for the dependent supergroup → group dropdown.
        $groups = $db->table('dims')->select('supergroup', 'group_desc')->get();

        return view('pages.insights', [
            'supergroups' => $groups->pluck('supergroup')->unique()->filter()->values(),
            'groups' => $groups, // supergroup + group_desc, for dependent dropdown
            'acuteChronic' => collect(json_decode($meta['acute_chronic'] ?? '[]')),
            'origins' => collect(json_decode($meta['indian_mnc'] ?? '[]')),
            'matLabel' => $meta['mat_label'] ?? '',
            'builtAt' => $meta['built_at'] ?? null,
            'months' => json_decode($meta['months'] ?? '[]'),
        ]);
    }

    /** JSON endpoint the dashboard fetches whenever filters change. */
    public function data(Request $request): JsonResponse
    {
        $db = $this->db();
        [$where, $bindings] = $this->buildWhere($request);
        $whereSql = $where ? ('WHERE '.$where) : '';

        $monthCols = json_decode($db->table('meta')->where('key', 'month_cols')->value('value') ?? '[]', true);
        $monthLabels = json_decode($db->table('meta')->where('key', 'months')->value('value') ?? '[]', true);
        $scale = (float) ($db->table('meta')->where('key', 'scale')->value('value') ?: 1);

        // Headline KPIs.
        $kpi = $db->selectOne(
            "SELECT COALESCE(SUM(mat_value),0) mat, COALESCE(SUM(prev_mat_value),0) prev, COUNT(*) packs
             FROM packs {$whereSql}",
            $bindings
        );
        $universe = (float) $db->table('packs')->sum('mat_value');
        $growth = $kpi->prev > 0 ? (($kpi->mat - $kpi->prev) / $kpi->prev) * 100 : null;
        $share = $universe > 0 ? ($kpi->mat / $universe) * 100 : 0;

        // Monthly trend across the filtered set (one scan, all month columns summed).
        $sumExprs = implode(', ', array_map(fn ($c) => "COALESCE(SUM(\"{$c}\"),0)", $monthCols));
        $trendRow = $db->selectOne("SELECT {$sumExprs} AS _ FROM packs {$whereSql}", $bindings);
        $trendVals = array_values((array) $trendRow);
        $trend = [];
        foreach ($monthLabels as $i => $label) {
            // Monthly columns are stored scaled-to-integer; divide back to ₹.
            $trend[] = ['label' => $label, 'value' => round(((float) ($trendVals[$i] ?? 0)) / $scale, 4)];
        }

        return response()->json([
            'kpi' => [
                'mat' => round((float) $kpi->mat, 3),
                'prev' => round((float) $kpi->prev, 3),
                'growth' => $growth === null ? null : round($growth, 1),
                'share' => round($share, 2),
                'packs' => (int) $kpi->packs,
            ],
            'trend' => $trend,
            'top' => [
                'molecule' => $this->topBy($db, ['molecule_desc'], $whereSql, $bindings, $universe, 10),
                'brand' => $this->topBy($db, ['brands', 'company'], $whereSql, $bindings, $universe, 50),
                'company' => $this->topBy($db, ['company', 'indian_mnc'], $whereSql, $bindings, $universe, 10),
                'group' => $this->topBy($db, ['supergroup'], $whereSql, $bindings, $universe, 10),
            ],
            'packs' => $db->select(
                "SELECT molecule_desc, brands, pack_desc, company, mat_value,
                        CASE WHEN prev_mat_value>0 THEN ROUND((mat_value-prev_mat_value)/prev_mat_value*100,1) END growth
                 FROM packs {$whereSql} ORDER BY mat_value DESC LIMIT 50",
                $bindings
            ),
        ]);
    }

    /** Top-N rollup for a set of dimension columns over the filtered universe. */
    private function topBy($db, array $dims, string $whereSql, array $bindings, float $universe, int $limit = 10): array
    {
        $sel = implode(', ', array_map(fn ($d) => "\"{$d}\"", $dims));
        $rows = $db->select(
            "SELECT {$sel},
                    SUM(mat_value) mat, SUM(prev_mat_value) prev, COUNT(*) packs
             FROM packs {$whereSql}
             GROUP BY {$sel}
             ORDER BY mat DESC
             LIMIT {$limit}",
            $bindings
        );

        return array_map(function ($r) use ($dims, $universe) {
            $r = (array) $r;
            $r['mat'] = round((float) $r['mat'], 3);
            $r['growth'] = $r['prev'] > 0 ? round((($r['mat'] - $r['prev']) / $r['prev']) * 100, 1) : null;
            $r['share'] = $universe > 0 ? round(($r['mat'] / $universe) * 100, 2) : 0;
            $r['label'] = trim((string) $r[$dims[0]]);
            $r['sub'] = isset($dims[1]) ? trim((string) $r[$dims[1]]) : null;
            unset($r['prev']);

            return $r;
        }, $rows);
    }

    /** Build a parameterised WHERE from the request filters. */
    private function buildWhere(Request $request): array
    {
        $clauses = [];
        $bindings = [];

        foreach (self::FILTERS as $param => $col) {
            $val = trim((string) $request->query($param, ''));
            if ($val !== '') {
                $clauses[] = "\"{$col}\" = ?";
                $bindings[] = $val;
            }
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $clauses[] = '(molecule_desc LIKE ? OR brands LIKE ? OR pack_desc LIKE ? OR company LIKE ?)';
            $like = '%'.$q.'%';
            array_push($bindings, $like, $like, $like, $like);
        }

        return [implode(' AND ', $clauses), $bindings];
    }
}
