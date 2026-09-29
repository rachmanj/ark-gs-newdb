<?php

namespace App\Console\Commands;

use App\Models\InventorySnapshot;
use Illuminate\Console\Command;

class InventoryPruneSnapshotsCommand extends Command
{
    protected $signature = 'inventory:prune-snapshots';

    protected $description = 'Delete inventory snapshots older than 12 months (items cascade).';

    public function handle(): int
    {
        $cutoff = now()->subMonths(12)->toDateString();

        $deleted = InventorySnapshot::query()
            ->where('snapshot_date', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} inventory snapshot(s) older than {$cutoff}.");

        return self::SUCCESS;
    }
}
