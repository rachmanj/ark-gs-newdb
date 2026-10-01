<?php

namespace Tests\Feature;

use App\Exports\SapReportSheet;
use App\Repositories\SapQueryRepository;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SapReportExportTest extends TestCase
{
    private const SAMPLE_QSTRING = <<<'SQL'
SELECT
    T0."DocNum" AS "DocNum",
    T0."DocDate" AS "DocDate",
    T0."LineTotal" AS "LineTotal"
FROM "OPDN" T0
WHERE T0."DocDate" >= '[%0]'
  AND T0."DocDate" <= '[%1]'
  AND T0."Comments" LIKE '%[%01] placeholder must stay%'
FOR BROWSE
SQL;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_run_replaces_date_tokens_and_strips_for_browse_without_touching_other_text(): void
    {
        $ouqrKey = 836;
        $executedSql = null;

        $connection = Mockery::mock();
        DB::shouldReceive('connection')->with('sap_sql')->andReturn($connection);

        $connection->shouldReceive('selectOne')
            ->once()
            ->with('SELECT QName, QString FROM OUQR WHERE IntrnalKey = ?', [$ouqrKey])
            ->andReturn((object) [
                'QName' => 'Purchase Order Complete (01)',
                'QString' => self::SAMPLE_QSTRING,
            ]);

        $connection->shouldReceive('select')
            ->once()
            ->with(Mockery::on(function (string $sql) use (&$executedSql) {
                $executedSql = $sql;

                return true;
            }))
            ->andReturn([]);

        $repository = new SapQueryRepository();
        $repository->run($ouqrKey, '2024-03-01', '2024-03-31');

        $this->assertNotNull($executedSql);
        $this->assertStringContainsString(">= '2024-03-01'", $executedSql);
        $this->assertStringContainsString("<= '2024-03-31'", $executedSql);
        $this->assertStringNotContainsString('[%0]', $executedSql);
        $this->assertStringNotContainsString('[%1]', $executedSql);
        $this->assertStringContainsString('%[%01] placeholder must stay%', $executedSql);
        $this->assertMatchesRegularExpression('/for\s+browse\s*$/i', self::SAMPLE_QSTRING);
        $this->assertDoesNotMatchRegularExpression('/for\s+browse\s*$/i', trim($executedSql));
    }

    public function test_run_rejects_invalid_date_format(): void
    {
        $connection = Mockery::mock();
        DB::shouldReceive('connection')->with('sap_sql')->andReturn($connection);

        $connection->shouldReceive('selectOne')
            ->once()
            ->andReturn((object) [
                'QName' => 'Test',
                'QString' => "SELECT 1 WHERE d >= '[%0]' AND d <= '[%1]'",
            ]);

        $repository = new SapQueryRepository();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid SAP report date format: 2024/03/01');

        $repository->run(836, '2024/03/01', '2024-03-31');
    }

    public function test_definition_throws_when_ouqr_key_is_missing(): void
    {
        $connection = Mockery::mock();
        DB::shouldReceive('connection')->with('sap_sql')->andReturn($connection);

        $connection->shouldReceive('selectOne')
            ->once()
            ->with('SELECT QName, QString FROM OUQR WHERE IntrnalKey = ?', [99999])
            ->andReturn(null);

        $repository = new SapQueryRepository();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SAP query definition not found for IntrnalKey 99999');

        $repository->definition(99999);
    }

    public function test_run_throws_when_date_range_query_lacks_start_token(): void
    {
        $ouqrKey = 836;

        $connection = Mockery::mock();
        DB::shouldReceive('connection')->with('sap_sql')->andReturn($connection);

        $connection->shouldReceive('selectOne')
            ->once()
            ->andReturn((object) [
                'QName' => 'Broken',
                'QString' => "SELECT * FROM OPDN WHERE DocDate <= '[%1]'",
            ]);

        $repository = new SapQueryRepository();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires date token [%0]');

        $repository->run($ouqrKey, '2024-01-01', '2024-01-31');
    }

    public function test_sap_report_sheet_headings_follow_first_row_key_order(): void
    {
        $rows = [
            ['ZCol' => 'z', 'ACol' => 'a', 'MCol' => 'm'],
            ['ZCol' => 'z2', 'ACol' => 'a2', 'MCol' => 'm2'],
        ];

        $sheet = new SapReportSheet('01. PO Complete', $rows);

        $this->assertSame(['ZCol', 'ACol', 'MCol'], $sheet->headings());
        $this->assertSame([
            ['z', 'a', 'm'],
            ['z2', 'a2', 'm2'],
        ], $sheet->array());
    }

    public function test_sap_report_sheet_preserves_zero_values(): void
    {
        $rows = [
            ['Qty' => 0, 'Amount' => 10],
        ];

        $sheet = new SapReportSheet('10. Inventory All Whs', $rows);

        $this->assertSame([['Qty', 'Amount']], [$sheet->headings()]);
        $this->assertSame([[0, 10]], $sheet->array());
    }

    public function test_run_normalizes_rows_to_associative_arrays(): void
    {
        $ouqrKey = 845;
        $sqlWithoutDates = 'SELECT T0."OnHand" AS "OnHand" FROM OITW T0 FOR BROWSE';

        $connection = Mockery::mock();
        DB::shouldReceive('connection')->with('sap_sql')->andReturn($connection);

        $connection->shouldReceive('selectOne')
            ->once()
            ->andReturn((object) [
                'QName' => 'Inventory',
                'QString' => $sqlWithoutDates,
            ]);

        $connection->shouldReceive('select')
            ->once()
            ->andReturn([(object) ['OnHand' => 0]]);

        $repository = new SapQueryRepository();
        $rows = $repository->run($ouqrKey, null, null);

        $this->assertSame([['OnHand' => 0]], $rows);
    }
}
