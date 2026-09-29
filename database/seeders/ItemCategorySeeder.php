<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Categories copied verbatim from the DDS application's prefix map.
     *
     * @return void
     */
    public function run()
    {
        $path = base_path('docs/item-categories-from-dds.json');

        if (! file_exists($path)) {
            $this->command?->error("Item categories source file not found: {$path}");

            return;
        }

        $categories = json_decode(file_get_contents($path), true) ?? [];

        foreach ($categories as $row) {
            ItemCategory::updateOrCreate(
                ['prefix' => $row['prefix']],
                [
                    'category' => $row['category'],
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
        }
    }
}
