<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use PDO;

/**
 * Builds the read-only `insights.sqlite` analytics store for the /insights
 * dashboard from the monthly IQVIA/AWACS pharma sales-audit xlsx.
 *
 * Runs LOCALLY only — the giant xlsx (~258 MB) is never deployed. openspout
 * streams the DATA sheet at constant memory, so this works without loading the
 * whole file. We keep only Value (₹) monthly columns + descriptors, then build
 * rollup tables so the dashboard reads pre-aggregated rows (sub-10ms queries).
 *
 * Usage:  php artisan insights:import "/path/to/MAR'26.xlsx"
 */
class ImportInsights extends Command
{
    protected $signature = 'insights:import {file : Path to the source xlsx}
        {--sheet=DATA : Worksheet name to read}';

    protected $description = 'Build insights.sqlite (Value-only, pack-level + rollups) from the monthly sales-audit xlsx';

    /**
     * Descriptor header => clean column name. Trimmed to only what the dashboard
     * filters, displays or searches — every extra text column is dead weight
     * repeated across 112k rows. (Dropped: manufact_desc, subgroup, pfc,
     * plain_combination, prod_code, nfc, short_description.)
     */
    private const DESCRIPTORS = [
        'MOLECULE_DESC' => 'molecule_desc',
        'PACK_DESC' => 'pack_desc',
        'BRANDS' => 'brands',
        'COMPANY' => 'company',
        'INDIAN_MNC' => 'indian_mnc',
        'GROUP' => 'group_desc',
        'SUPERGROUP' => 'supergroup',
        'ACUTE_CHRONIC' => 'acute_chronic',
    ];

    /**
     * Monthly Value is stored as an INTEGER scaled by this factor (₹ → ₹/1000),
     * which lets SQLite varint-pack small/zero values into 1–3 bytes instead of
     * REAL's fixed 8 — the single biggest size win. Aggregate error is ~0.005%
     * and the exact MAT totals are kept separately as REAL, so KPIs stay exact;
     * only the trend chart reads the scaled values (and divides back by SCALE).
     */
    private const SCALE = 1000;

    private const MONTH_NUM = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $dbPath = config('database.connections.insights.database');
        $this->info("Source : {$path}");
        $this->info("Target : {$dbPath}");

        // Fresh build every time — the store is a disposable artifact.
        DB::purge('insights');
        foreach (['', '-shm', '-wal'] as $suffix) {
            @unlink($dbPath.$suffix);
        }
        @mkdir(dirname($dbPath), 0755, true);
        touch($dbPath); // Laravel's SQLite connector requires the file to exist.

        $pdo = DB::connection('insights')->getPdo();
        $pdo->exec('PRAGMA journal_mode=OFF');
        $pdo->exec('PRAGMA synchronous=OFF');

        $reader = new Reader;
        $reader->open($path);

        $sheetName = $this->option('sheet');
        $sheet = null;
        foreach ($reader->getSheetIterator() as $s) {
            if ($s->getName() === $sheetName) {
                $sheet = $s;
                break;
            }
        }
        if ($sheet === null) {
            $this->error("Sheet '{$sheetName}' not found.");

            return self::FAILURE;
        }

        // openspout's XLSX iterator is forward-only, so we make ONE pass: the
        // first row builds the column maps + schema + prepared insert, the rest
        // stream straight into packs.
        $descPos = [];
        $monthCols = $matCols = $prevCols = [];
        $matIdx = $prevIdx = $monthIdx = [];
        $insert = null;
        $n = 0;
        $t0 = microtime(true);

        foreach ($sheet->getRowIterator() as $rowNum => $row) {
            $cells = $row->toArray();

            if ($rowNum === 1) {
                $headerCells = array_map(static fn ($v) => trim((string) $v), $cells);

                foreach (self::DESCRIPTORS as $header => $col) {
                    $i = array_search($header, $headerCells, true);
                    if ($i !== false) {
                        $descPos[$col] = $i;
                    }
                }

                // Monthly Value columns are bare "MON'YY" (UNIT/QTY carry a prefix).
                foreach ($headerCells as $i => $h) {
                    if (preg_match("/^([A-Z]{3})'(\d{2})$/", $h, $m)) {
                        $year = 2000 + (int) $m[2];
                        $mon = self::MONTH_NUM[$m[1]];
                        $monthCols[] = [
                            'col' => sprintf('v_%04d_%02d', $year, $mon),
                            'idx' => $i,
                            'label' => $h,
                            'sort' => $year * 100 + $mon,
                        ];
                    }
                }
                usort($monthCols, fn ($a, $b) => $a['sort'] <=> $b['sort']);

                if (count($monthCols) < 12) {
                    $this->error('Could not find the monthly Value columns. Aborting.');

                    return self::FAILURE;
                }
                $this->info('Months : '.count($monthCols).' ('.$monthCols[0]['label'].' → '.end($monthCols)['label'].')');

                // Last 12 = current MAT, previous 12 = prior MAT (for YoY growth).
                $matCols = array_slice($monthCols, -12);
                $prevCols = array_slice($monthCols, -24, 12);
                $matIdx = array_column($matCols, 'idx');
                $prevIdx = array_column($prevCols, 'idx');
                $monthIdx = array_column($monthCols, 'idx');

                $this->buildSchema($pdo, array_keys($descPos), $monthCols);

                $cols = array_merge(array_keys($descPos), array_column($monthCols, 'col'), ['mat_value', 'prev_mat_value']);
                $placeholders = implode(',', array_fill(0, count($cols), '?'));
                $insert = $pdo->prepare('INSERT INTO packs ("'.implode('","', $cols).'") VALUES ('.$placeholders.')');

                $pdo->beginTransaction();

                continue;
            }

            $values = [];
            foreach ($descPos as $idx) {
                $values[] = isset($cells[$idx]) ? trim((string) $cells[$idx]) : null;
            }
            $matSum = 0.0;
            $prevSum = 0.0;
            // Monthly values stored scaled-to-integer; MAT totals kept exact (REAL).
            foreach ($monthIdx as $idx) {
                $values[] = (int) round($this->num($cells[$idx] ?? null) * self::SCALE);
            }
            foreach ($matIdx as $idx) {
                $matSum += $this->num($cells[$idx] ?? null);
            }
            foreach ($prevIdx as $idx) {
                $prevSum += $this->num($cells[$idx] ?? null);
            }
            $values[] = $matSum;
            $values[] = $prevSum;

            $insert->execute($values);

            if (++$n % 5000 === 0) {
                $pdo->commit();
                $pdo->beginTransaction();
                $this->output->write("\r  ...{$n} rows");
            }
        }
        $pdo->commit();
        $reader->close();
        $this->output->write("\r");
        $this->info("Inserted {$n} packs in ".round(microtime(true) - $t0)."s");

        $this->buildAggregates($pdo, $monthCols);

        $sizeMb = round(filesize($dbPath) / 1e6, 1);
        $this->newLine();
        $this->info("✓ Built {$dbPath} — {$sizeMb} MB");

        return self::SUCCESS;
    }

    private function num(mixed $v): float
    {
        if ($v === null || $v === '' || $v === '0') {
            return 0.0;
        }

        return is_numeric($v) ? (float) $v : 0.0;
    }

    private function buildSchema(PDO $pdo, array $descCols, array $monthCols): void
    {
        $defs = [];
        foreach ($descCols as $c) {
            $defs[] = "\"{$c}\" TEXT";
        }
        foreach ($monthCols as $mc) {
            // INTEGER (scaled) — far smaller than REAL for sparse/small values.
            $defs[] = "\"{$mc['col']}\" INTEGER DEFAULT 0";
        }
        $defs[] = '"mat_value" REAL DEFAULT 0';
        $defs[] = '"prev_mat_value" REAL DEFAULT 0';
        $pdo->exec('DROP TABLE IF EXISTS packs');
        $pdo->exec('CREATE TABLE packs (id INTEGER PRIMARY KEY, '.implode(', ', $defs).')');
    }

    /**
     * Lean companions to the packs table. We deliberately keep NO per-dimension
     * rollups or column indexes: the dashboard's top-N queries are GROUP BYs that
     * scan the (filtered) set anyway, so indexes bought little but cost ~30 MB.
     * A full scan of 112k rows is ~0.1s — fine for an internal tool. We keep only
     * a tiny `dims` table for the dependent dropdown plus a `meta` table.
     */
    private function buildAggregates(PDO $pdo, array $monthCols): void
    {
        $pdo->exec('DROP TABLE IF EXISTS dims');
        $pdo->exec('CREATE TABLE dims AS SELECT DISTINCT supergroup, group_desc FROM packs ORDER BY supergroup, group_desc');
        $this->line('  dims ✓');

        $pdo->exec('DROP TABLE IF EXISTS meta');
        $pdo->exec('CREATE TABLE meta (key TEXT PRIMARY KEY, value TEXT)');
        $meta = $pdo->prepare('INSERT INTO meta VALUES (?, ?)');
        $meta->execute(['months', json_encode(array_column($monthCols, 'label'))]);
        $meta->execute(['month_cols', json_encode(array_column($monthCols, 'col'))]);
        $meta->execute(['mat_label', end($monthCols)['label']]);
        $meta->execute(['scale', (string) self::SCALE]);
        $meta->execute(['built_at', now()->toIso8601String()]);

        // Distinct option lists for the small dropdowns (cheap, avoids a scan per page load).
        foreach (['acute_chronic', 'indian_mnc'] as $col) {
            $vals = $pdo->query("SELECT DISTINCT \"{$col}\" FROM packs WHERE \"{$col}\" <> '' ORDER BY \"{$col}\"")
                ->fetchAll(PDO::FETCH_COLUMN);
            $meta->execute([$col, json_encode($vals)]);
        }
        $this->line('  meta ✓');

        $pdo->exec('VACUUM');
    }
}
