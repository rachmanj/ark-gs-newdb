<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventorySnapshot;
use Carbon\CarbonImmutable;

class InventorySummaryController extends Controller
{
    private const OTHERS_LABEL = 'Others';

    private const MAX_WAREHOUSE_BARS = 12;

    private const NO_PROJECT_LABEL = '(tanpa project)';

    public function index()
    {
        $latestSnapshot = InventorySnapshot::query()
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        $snapshot = InventorySnapshot::query()
            ->where('status', 'success')
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        return view('inventory-summary.index', [
            'warningMessage' => $this->buildWarningMessage($latestSnapshot, $snapshot),
            'snapshot' => $snapshot,
            'kpi' => $this->buildKpi($snapshot),
            'warehouseChart' => $this->buildWarehouseValueChart($snapshot),
            'projectInstockChart' => $this->buildProjectInstockChart($snapshot),
            'categoryValueChart' => $this->buildCategoryValueChart($snapshot),
            'monthlyTrendChart' => $this->buildMonthlyTrendChart(),
            'instockPivot' => $this->buildPivot($snapshot, 'instock'),
            'valuePivot' => $this->buildPivot($snapshot, 'total_value'),
        ]);
    }

    private function buildWarningMessage(?InventorySnapshot $latest, ?InventorySnapshot $success): ?string
    {
        if (! $success) {
            return $latest
                ? "The latest inventory snapshot (run on {$latest->snapshot_date->format('d M Y')}) failed. No successful snapshot is available yet, so the figures below cannot be shown."
                : 'No inventory snapshot has been recorded yet. The figures below cannot be shown.';
        }

        if ($latest && $latest->status !== 'success') {
            return "The latest inventory snapshot (run on {$latest->snapshot_date->format('d M Y')}) failed. Showing data from the last successful snapshot on {$success->snapshot_date->format('d M Y')} instead.";
        }

        return null;
    }

    private function buildKpi(?InventorySnapshot $snapshot): array
    {
        return [
            'total_items' => $snapshot->row_count ?? 0,
            'total_value' => $snapshot ? (float) $snapshot->total_value : 0.0,
            'warehouse_count' => $snapshot
                ? InventoryItem::query()->where('snapshot_id', $snapshot->id)->distinct()->count('whs_code')
                : 0,
            'snapshot_date' => $snapshot?->snapshot_date,
        ];
    }

    private function buildWarehouseValueChart(?InventorySnapshot $snapshot): array
    {
        if (! $snapshot) {
            return ['labels' => [], 'values' => []];
        }

        $rows = InventoryItem::query()
            ->where('snapshot_id', $snapshot->id)
            ->selectRaw('whs_code, whs_name, SUM(total_value) as value')
            ->groupBy('whs_code', 'whs_name')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->whs_name ?: ($row->whs_code ?: 'Unknown'),
                'value' => (float) $row->value,
            ]);

        if ($rows->count() > self::MAX_WAREHOUSE_BARS) {
            $kept = $rows->take(self::MAX_WAREHOUSE_BARS - 1);
            $othersValue = $rows->slice(self::MAX_WAREHOUSE_BARS - 1)->sum('value');
            $rows = $kept->push(['label' => self::OTHERS_LABEL, 'value' => $othersValue]);
        }

        return [
            'labels' => $rows->pluck('label')->values()->all(),
            'values' => $rows->pluck('value')->values()->all(),
        ];
    }

    private function buildProjectInstockChart(?InventorySnapshot $snapshot): array
    {
        if (! $snapshot) {
            return ['labels' => [], 'values' => []];
        }

        $rows = InventoryItem::query()
            ->where('snapshot_id', $snapshot->id)
            ->selectRaw("COALESCE(project, '".self::NO_PROJECT_LABEL."') as project_label, SUM(instock) as value")
            ->groupBy('project_label')
            ->orderByDesc('value')
            ->get();

        return [
            'labels' => $rows->pluck('project_label')->values()->all(),
            'values' => $rows->pluck('value')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    private function buildCategoryValueChart(?InventorySnapshot $snapshot): array
    {
        if (! $snapshot) {
            return ['labels' => [], 'values' => []];
        }

        $rows = InventoryItem::query()
            ->where('snapshot_id', $snapshot->id)
            ->selectRaw('category, SUM(total_value) as value')
            ->groupBy('category')
            ->orderByDesc('value')
            ->get();

        return [
            'labels' => $rows->pluck('category')->values()->all(),
            'values' => $rows->pluck('value')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    private function buildMonthlyTrendChart(): array
    {
        $labels = [];
        $values = [];
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $month = $start->addMonths($i);
            $labels[] = $month->format('M Y');

            $snap = InventorySnapshot::query()
                ->where('status', 'success')
                ->whereYear('snapshot_date', $month->year)
                ->whereMonth('snapshot_date', $month->month)
                ->orderByDesc('snapshot_date')
                ->orderByDesc('id')
                ->first();

            $values[] = $snap ? (float) $snap->total_value : null;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  'instock'|'total_value'  $column
     */
    private function buildPivot(?InventorySnapshot $snapshot, string $column): array
    {
        if (! $snapshot) {
            return ['projects' => [], 'categories' => [], 'matrix' => []];
        }

        $rows = InventoryItem::query()
            ->where('snapshot_id', $snapshot->id)
            ->selectRaw("COALESCE(project, '".self::NO_PROJECT_LABEL."') as project_label, category, SUM({$column}) as value")
            ->groupBy('project_label', 'category')
            ->get();

        $projects = $rows->pluck('project_label')->unique()->sort()->values()->all();
        $categories = $rows->pluck('category')->unique()->sort()->values()->all();

        $matrix = [];
        foreach ($projects as $project) {
            foreach ($categories as $category) {
                $matrix[$project][$category] = null;
            }
        }

        foreach ($rows as $row) {
            $matrix[$row->project_label][$row->category] = (float) $row->value;
        }

        return ['projects' => $projects, 'categories' => $categories, 'matrix' => $matrix];
    }

    /**
     * Compact Indonesian-style magnitude suffix (Rb/Jt/M/T), used for the KPI
     * headline numbers. No equivalent helper existed elsewhere in the app.
     */
    public static function formatCompactValue(float $value): string
    {
        $abs = abs($value);
        $divisor = 1;
        $suffix = '';

        if ($abs >= 1_000_000_000_000) {
            $divisor = 1_000_000_000_000;
            $suffix = ' T';
        } elseif ($abs >= 1_000_000_000) {
            $divisor = 1_000_000_000;
            $suffix = ' M';
        } elseif ($abs >= 1_000_000) {
            $divisor = 1_000_000;
            $suffix = ' Jt';
        } elseif ($abs >= 1_000) {
            $divisor = 1_000;
            $suffix = ' Rb';
        }

        return number_format($value / $divisor, $divisor === 1 ? 0 : 2, ',', '.').$suffix;
    }

    public static function formatRupiah(float $value): string
    {
        return 'Rp '.number_format($value, 2, ',', '.');
    }
}
