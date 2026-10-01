<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardYearlyController;
use App\Http\Controllers\YearlyHistoryController;
use App\Http\Controllers\YearlyIndexController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\TestCase;

class YearlyHiddenProjectsTest extends TestCase
{
    private const HIDDEN = ['023C', '026C'];

    public function test_yearly_index_reguler_and_grpo_exclude_hidden_projects(): void
    {
        $controller = new YearlyIndexController();

        $regulerProjects = $this->projectCodesFromRows(
            $controller->reguler_yearly()['reguler_yearly'] ?? [],
            'project'
        );
        $grpoProjects = $this->projectCodesFromRows(
            $controller->grpo_index()['grpo_yearly'] ?? [],
            'project'
        );

        foreach (self::HIDDEN as $code) {
            $this->assertNotContains($code, $regulerProjects, "Reguler yearly must not list {$code}");
            $this->assertNotContains($code, $grpoProjects, "GRPO yearly must not list {$code}");
        }
    }

    public function test_yearly_index_capex_and_npi_still_include_hidden_projects(): void
    {
        $controller = new YearlyIndexController();

        $capexProjects = $this->projectCodesFromRows(
            $controller->capex_yearly()['capex'] ?? [],
            'project'
        );
        $npiProjects = $this->projectCodesFromRows(
            $controller->npi_index()['npi'] ?? [],
            'project'
        );

        foreach (self::HIDDEN as $code) {
            $this->assertContains($code, $capexProjects, "Capex yearly must still list {$code}");
            $this->assertContains($code, $npiProjects, "NPI yearly must still list {$code}");
        }
    }

    public function test_yearly_history_reguler_and_grpo_exclude_hidden_projects(): void
    {
        $year = $this->resolveHistoryYear();
        $controller = new YearlyHistoryController();

        $regulerProjects = $this->projectCodesFromRows(
            $controller->reguler_history_yearly($year)['reguler_yearly'] ?? [],
            'project'
        );
        $grpoProjects = $this->projectCodesFromRows(
            $controller->grpo_history_yearly($year)['grpo_yearly'] ?? [],
            'project'
        );

        foreach (self::HIDDEN as $code) {
            $this->assertNotContains($code, $regulerProjects, "History reguler for {$year} must not list {$code}");
            $this->assertNotContains($code, $grpoProjects, "History GRPO for {$year} must not list {$code}");
        }
    }

    public function test_yearly_history_capex_and_npi_still_include_hidden_projects(): void
    {
        $year = $this->resolveHistoryYear();
        $controller = new YearlyHistoryController();

        $capexProjects = $this->projectCodesFromRows(
            $controller->capex_history_yearly($year)['capex'] ?? [],
            'project'
        );
        $npiProjects = $this->projectCodesFromRows(
            $controller->npi_history_yearly($year)['npi'] ?? [],
            'project'
        );

        foreach (self::HIDDEN as $code) {
            $this->assertContains($code, $capexProjects, "History capex for {$year} must still list {$code}");
            $this->assertContains($code, $npiProjects, "History NPI for {$year} must still list {$code}");
        }
    }

    public function test_dashboard_yearly_current_year_stats_exclude_hidden_projects_from_totals(): void
    {
        $currentYear = Carbon::now()->year;
        $includeProjects = ['017C', '021C', '022C', '025C', '026C', 'APS', '023C'];
        $visibleProjects = array_values(array_diff($includeProjects, self::HIDDEN));

        $expectedBudget = (float) DB::table('budgets')
            ->whereIn('project_code', $visibleProjects)
            ->where('budget_type_id', 2)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $expectedPoSent = (float) DB::table('histories')
            ->whereIn('project_code', $visibleProjects)
            ->where('periode', 'yearly')
            ->where('gs_type', 'po_sent')
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $expectedGrpo = (float) DB::table('histories')
            ->whereIn('project_code', $visibleProjects)
            ->where('periode', 'yearly')
            ->where('gs_type', 'grpo_amount')
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $controller = new DashboardYearlyController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getCurrentYearStats');
        $method->setAccessible(true);
        $stats = $method->invoke($controller);

        $this->assertSame($expectedBudget, (float) $stats['totalBudget']);
        $this->assertSame($expectedPoSent, (float) $stats['totalPoSent']);
        $this->assertSame($expectedGrpo, (float) $stats['totalGrpo']);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return list<string>
     */
    private function projectCodesFromRows(array $rows, string $key): array
    {
        return array_values(array_map(static fn (array $row): string => (string) $row[$key], $rows));
    }

    private function resolveHistoryYear(): int
    {
        $row = DB::table('histories')
            ->where('periode', 'yearly')
            ->orderByDesc('date')
            ->value('date');

        if ($row === null) {
            return Carbon::now()->year - 1;
        }

        return (int) date('Y', strtotime((string) $row));
    }
}
