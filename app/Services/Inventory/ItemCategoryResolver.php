<?php

namespace App\Services\Inventory;

use App\Models\ItemCategory;

class ItemCategoryResolver
{
    public const UNCATEGORIZED = '(tanpa kategori)';

    /**
     * Per-instance cache only (not static) so the map never survives past
     * the lifetime of a single resolver instance/request.
     *
     * @var array<string, string>|null
     */
    private ?array $prefixMap = null;

    /**
     * Resolves an item code to a category, mirroring the DDS prefix-matching
     * order: full code, then segment before the first hyphen, then the
     * 3-letter prefix, then the 2-letter prefix.
     */
    public function resolve(?string $itemCode): string
    {
        $code = strtoupper(trim((string) $itemCode));

        if ($code === '') {
            return self::UNCATEGORIZED;
        }

        $map = $this->buildPrefixMap();

        $candidates = [$code];

        $hyphenSegment = strtok($code, '-');
        if ($hyphenSegment !== false && $hyphenSegment !== $code) {
            $candidates[] = $hyphenSegment;
        }

        $candidates[] = substr($code, 0, 3);
        $candidates[] = substr($code, 0, 2);

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && array_key_exists($candidate, $map)) {
                return $map[$candidate];
            }
        }

        return self::UNCATEGORIZED;
    }

    /**
     * Loaded once per resolver instance (not a static property), so a fresh
     * instance always sees current data and nothing leaks across requests.
     */
    private function buildPrefixMap(): array
    {
        if ($this->prefixMap === null) {
            $this->prefixMap = ItemCategory::query()
                ->where('is_active', true)
                ->pluck('category', 'prefix')
                ->all();
        }

        return $this->prefixMap;
    }
}
