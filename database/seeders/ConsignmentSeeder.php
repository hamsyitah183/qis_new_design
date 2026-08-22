<?php

namespace Database\Seeders;

use Carbon\Carbon;
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

        // Map category codes to their public_code.id
        $categoryMap = [];
        $categories = DB::table('public_code')
            ->where('cate_name', 'consignment_category')
            ->get(['id', 'cate_code']);

        foreach ($categories as $cat) {
            $categoryMap[$cat->cate_code] = $cat->id;
        }

        $items = [
            // ===== Category: Spices (cate_code = 1) =====
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Garlic', 'scientific_name' => 'Allium sativum'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Big Onion', 'scientific_name' => 'Allium cepa'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Shallot', 'scientific_name' => 'Allium cepa var. aggregatum'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Ginger', 'scientific_name' => 'Zingiber officinale'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Lemongrass', 'scientific_name' => 'Cymbopogon citratus'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Fresh Turmeric', 'scientific_name' => 'Curcuma longa'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Galangal', 'scientific_name' => 'Alpinia galanga'],
            ['category' => $categoryMap['1'] ?? 1, 'item_name' => 'Pepper', 'scientific_name' => 'Piper nigrum'],

            // ===== Category: Vegetables (cate_code = 2) =====
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Chili Pepper', 'scientific_name' => 'Capsicum annuum / Capsicum frutescens'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Mustard Greens', 'scientific_name' => 'Brassica rapa subsp. parachinensis'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Chinese Kale', 'scientific_name' => 'Brassica oleracea var. alboglabra'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Spinach', 'scientific_name' => 'Amaranthus tricolor'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Water Spinach', 'scientific_name' => 'Ipomoea aquatica'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Celery Leaves', 'scientific_name' => 'Apium graveolens'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Spring Onion', 'scientific_name' => 'Allium fistulosum'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Okra', 'scientific_name' => 'Abelmoschus esculentus'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Cucumber', 'scientific_name' => 'Cucumis sativus'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Eggplant', 'scientific_name' => 'Solanum melongena'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Long Bean', 'scientific_name' => 'Vigna unguiculata subsp. sesquipedalis'],
            ['category' => $categoryMap['2'] ?? 2, 'item_name' => 'Tomato', 'scientific_name' => 'Solanum lycopersicum'],

            // ===== Category: Fruits (cate_code = 3) =====
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Durian', 'scientific_name' => 'Durio zibethinus'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Banana', 'scientific_name' => 'Musa acuminata / Musa balbisiana'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Pineapple', 'scientific_name' => 'Ananas comosus'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Papaya', 'scientific_name' => 'Carica papaya'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Rambutan', 'scientific_name' => 'Nephelium lappaceum'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Mangosteen', 'scientific_name' => 'Garcinia mangostana'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Calamansi', 'scientific_name' => 'Citrus microcarpa'],
            ['category' => $categoryMap['3'] ?? 3, 'item_name' => 'Dragon Fruit', 'scientific_name' => 'Selenicereus undatus'],

            // ===== Category: Roots & Tubers (cate_code = 4) =====
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'Cassava', 'scientific_name' => 'Manihot esculenta'],
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'Sweet Potato', 'scientific_name' => 'Ipomoea batatas'],
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'Taro', 'scientific_name' => 'Colocasia esculenta'],
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'Carrot', 'scientific_name' => 'Daucus carota'],
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'White Radish', 'scientific_name' => 'Raphanus sativus'],
            ['category' => $categoryMap['4'] ?? 4, 'item_name' => 'Raw Green Coffee Beans', 'scientific_name' => 'Coffea liberica / Coffea arabica'],
        ];

        // Prepare data for insertion
        $data = array_map(function ($item) use ($now) {
            return [
                'category'          => $item['category'],
                'item_name'         => $item['item_name'],
                'scientific_name'   => $item['scientific_name'],
                'addional_condition'=> '',
                'quantity_limit'    => null,
                'date_limit'        => null, // Use date_limit (not start_date/end_date)
                'country'           => json_encode(['SWK']),
                'usage'             => '',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }, $items);

        DB::table('consignment_conditions')->insert($data);
    }
}