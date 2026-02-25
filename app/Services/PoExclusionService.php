<?php

namespace App\Services;

use App\Models\PoExclusion;
use Illuminate\Database\Query\Builder;

class PoExclusionService
{
    public function getExcludedPoNumbers(): array
    {
        return PoExclusion::pluck('po_no')->toArray();
    }

    public function applyExclusion(Builder $query): Builder
    {
        $excluded = $this->getExcludedPoNumbers();
        if (!empty($excluded)) {
            $query->whereNotIn('po_no', $excluded);
        }
        return $query;
    }
}
