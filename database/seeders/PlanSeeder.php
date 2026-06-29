<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Support\PlanCatalog;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PlanCatalog::definitions() as $id => $def) {
            Plan::updateOrCreate(['id' => $id], [
                'name' => $def['name'],
                'price' => $def['price'],
                'duration' => $def['duration'],
                'tag' => $def['tag'],
                'period' => $def['period'],
                'popular' => $def['popular'],
                'features' => $def['features'],
                'entitlements' => $def['entitlements'],
            ]);
        }

        echo "✅ Plans seeded with tier entitlements (S-Tier / Alpha / Interstellar)\n";
    }
}
