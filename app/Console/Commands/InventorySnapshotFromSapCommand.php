<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\InventorySnapshot;
use App\Repositories\SapInventoryRepository;
use App\Services\Inventory\ItemCategoryResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventorySnapshotFromSapCommand extends Command
{
    protected $signature = 'inventory:snapshot-from-sap';

    protected $description = 'Fetch current SAP inventory and store it as a new inventory snapshot.';

    private const INSERT_CHUNK_SIZE = 500;

    public function handle(SapInventoryRepository $repository, ItemCategoryResolver $resolver): int
    {
        $startedAt = microtime(true);

        try {
            $rows = $repository->fetchAll();
        } catch (Throwable $e) {
            Log::error('inventory:snapshot-from-sap fetch failed', ['exception' => $e->getMessage()]);

            return $this->recordFailure($e->getMessage(), $startedAt);
        }

        $rowCount = count($rows);
        $totalValue = $this->sumTotalValue($rows);

        try {
            DB::transaction(function () use ($rows, $rowCount, $totalValue, $resolver, $startedAt) {
                $snapshot = InventorySnapshot::create([
                    'snapshot_date' => now()->toDateString(),
                    'status' => 'success',
                    'row_count' => $rowCount,
                    'total_value' => $totalValue,
                    'error_message' => null,
                    'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);

                $now = now();

                foreach (array_chunk($rows, self::INSERT_CHUNK_SIZE) as $chunk) {
                    $insertRows = array_map(
                        fn (array $row) => $this->toItemRow($row, $snapshot->id, $resolver, $now),
                        $chunk
                    );

                    InventoryItem::insert($insertRows);
                }

                $snapshot->update([
                    'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            });
        } catch (Throwable $e) {
            Log::error('inventory:snapshot-from-sap save failed', ['exception' => $e->getMessage()]);

            return $this->recordFailure($e->getMessage(), $startedAt);
        }

        $this->info("Snapshot saved: {$rowCount} rows, total value {$totalValue}");

        return self::SUCCESS;
    }

    private function toItemRow(array $row, int $snapshotId, ItemCategoryResolver $resolver, $now): array
    {
        return [
            'snapshot_id' => $snapshotId,
            'model_no' => $row['model_no'] ?? null,
            'unit_no' => $row['unit_no'] ?? null,
            'item_code' => $row['item_code'] ?? null,
            'item_name' => $row['item_name'] ?? null,
            'category' => $resolver->resolve($row['item_code'] ?? null),
            'uom' => $row['uom'] ?? null,
            'instock' => $this->toDecimalString($row['instock'] ?? 0, 4),
            'committed' => $this->toDecimalString($row['committed'] ?? 0, 4),
            'ordered' => $this->toDecimalString($row['ordered'] ?? 0, 4),
            'currency' => $row['currency'] ?? null,
            'last_price' => $row['last_price'] !== null ? $this->toDecimalString($row['last_price'], 4) : null,
            'total_value' => $this->toDecimalString($row['total_value'] ?? 0, 2),
            'whs_code' => $row['whs_code'] ?? null,
            'whs_name' => $row['whs_name'] ?? null,
            'project' => $row['project'] ?? null,
            'status' => $row['status'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Sums raw (unrounded) SAP totals at high precision, truncating to 2
     * decimals only once at the end (matches decimal(20,2) column scale
     * without introducing per-row or half-up rounding drift).
     */
    private function sumTotalValue(array $rows): string
    {
        $sum = '0';

        foreach ($rows as $row) {
            $sum = bcadd($sum, (string) ($row['total_value'] ?? 0), 10);
        }

        return bcadd($sum, '0', 2);
    }

    private function toDecimalString($value, int $scale): string
    {
        return bcadd((string) $value, '0', $scale);
    }

    private function recordFailure(string $message, float $startedAt): int
    {
        InventorySnapshot::create([
            'snapshot_date' => now()->toDateString(),
            'status' => 'failed',
            'row_count' => 0,
            'total_value' => 0,
            'error_message' => mb_substr($message, 0, 65000),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        $this->error($message);

        return self::FAILURE;
    }
}
