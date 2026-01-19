<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $conditions = [
            // Category 1: Fresh Fruit
            [
                'category' => 1,
                'item_name' => 'DURIAN (FRESH/CHILLED)',
                'addional_condition' => '<pre>1) Must be free from soil and organic debris.
2) Consignment is subject to physical inspection at the point of entry in Sarawak.
3) The Phytosanitary Certificate must state the fruit is free from Durian Seed Borer (Mudaria luteileprosa).</pre>',
                'quantity_limit' => 500.0,
                'date_limit' => null,
                'country' => json_encode(['SMY', 'SWK']),
                'usage' => json_encode(['Consumption', 'Retail']),
            ],
            // Category 3: Fresh Vegetables
            [
                'category' => 3,
                'item_name' => 'CABBAGE',
                'addional_condition' => '<pre>1) Subject to random sampling for pesticide residue analysis.
2) Must be accompanied by a valid Import Permit reference: (IPXXXXX).</pre>',
                'quantity_limit' => 1000.0,
                'date_limit' => null,
                'country' => json_encode(['SMY','SWK']),
                'usage' => json_encode(['Consumption']),
            ],
            // Category 4: Planting Material
            [
                'category' => 4,
                'item_name' => 'ORCHID SEEDLINGS',
                'addional_condition' => '<pre>1) Must be grown in sterile culture medium.
2) Must be free from viruses and bacterial soft rot.
3) Subject to 3-month post-entry quarantine observation.</pre>',
                'quantity_limit' => 100.0,
                'date_limit' => Carbon::parse('2026-12-31'),
                'country' => json_encode(['SMY',  'SWK']),
                'usage' => json_encode(['Planting', 'Research']),
            ],
            // Category 6: Others (Example: Processed products)
            [
                'category' => 6,
                'item_name' => 'DRIED COCOA BEANS',
                'addional_condition' => '<pre>1) Must be commercially packed and clean.
2) Must be accompanied by a declaration of heat treatment.</pre>',
                'quantity_limit' => null,
                'date_limit' => null,
                'country' => json_encode(['SMY',  'SWK']),
                'usage' => json_encode(['Processing', 'Manufacturing']),
            ],
        ];

        // Add timestamps to each entry
        $data = array_map(function ($item) use ($now) {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $conditions);

        DB::table('consignment_conditions')->insert($data);
    }
}
