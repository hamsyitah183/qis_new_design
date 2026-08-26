<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\IpUses;

class IpUsesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Option A: Insert from the existing ip_condition table (dynamic)
        $this->seedFromIpCondition();

        // Option B: If you prefer a static list, uncomment and use this instead:
        // $this->seedStaticList();
    }

    /**
     * Dynamically extract all distinct uses from ip_condition.
     */
    private function seedFromIpCondition(): void
    {
        $allUses = DB::table('ip_condition')->pluck('usage');
        $uniqueUses = [];

        foreach ($allUses as $usageJson) {
            $decoded = json_decode($usageJson, true);
            if (is_array($decoded)) {
                foreach ($decoded as $use) {
                    $trimmed = trim($use);
                    if (!empty($trimmed)) {
                        $uniqueUses[$trimmed] = true;
                    }
                }
            }
        }

        $uses = array_keys($uniqueUses);

        foreach ($uses as $use) {
            IpUses::firstOrCreate(['name' => $use]);
        }

        $this->command->info('Seeded ' . count($uses) . ' unique uses from ip_condition.');
    }

    /**
     * Static list of common uses (fallback).
     */
    private function seedStaticList(): void
    {
        $uses = [
            'Fresh Produce',
            'For Animal Consumption',
            'Planting Material',
            // Add more if needed
        ];

        foreach ($uses as $use) {
            IpUses::firstOrCreate(['name' => $use]);
        }

        $this->command->info('Seeded static uses list.');
    }
}