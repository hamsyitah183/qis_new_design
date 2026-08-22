<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $purpose = [
            'Commercial (Animal Feed)',
            'Commercial (Decoration)',
            'Commercial (Human consumption)',
            'Commercial (Landscaping)',
            'Commercial (Planting material)',
            'Individual (Animal Feed)',
            'Individual (Personal consumption)',
            'Individual (Landscaping)',
            'Individual (Planting material)',
            'Individual (Decoration)',
            'Material for product manufacturing',
            'Research (Downstream product)',
            'Research (Lab analysis)'
        ];

        foreach ($purpose as $index => $desc) {
            DB::table('public_code')->insert([
                'cate_name' => 'consignment_purpose',
                'cate_code' => (string)($index + 1),
                'description' => $desc,
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };

        $conditionCategory = [
            'Cuttings',
            'Dried bean',
            'Dried fruit',
            'Fresh fruit (Seed removed)',
            'Fresh fruit (Whole fruit)',
            'Fresh vegetable',
            'Frozen fruit (Seed removed)',
            'Frozen fruit (Whole fruit)',
            'Frozen vegetable',
            'Ramet',
            'Sapling',
            'Seedlings',
            'Seeds',
            'Tissue culture'
        ];

        foreach ($conditionCategory as $index => $desc) {
            DB::table('public_code')->insert([
                'cate_name' => 'condition_category',
                'cate_code' => (string)($index + 1),
                'description' => $desc,
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };

        DB::table('public_code')->insert([
            // district_entry  consignment_purpose unit_measurement condition_category
            [
                'cate_name' => 'district_entry',
                'cate_code' => '1',
                'description' => 'Kota Kinabalu',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '2',
                'description' => 'Kudat',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '3',
                'description' => 'Sandakan',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '4',
                'description' => 'Lahad Datu',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '5',
                'description' => 'Tawau',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '6',
                'description' => 'Kunak',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '7',
                'description' => 'Semporna',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '8',
                'description' => 'Kuala Penyu',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'district_entry',
                'cate_code' => '9',
                'description' => 'Sipitang',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // [
            //     'cate_name' => 'consignment_purpose',
            //     'cate_code' => '1',
            //     'description' => 'Commercial (Trade)',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'consignment_purpose',
            //     'cate_code' => '2',
            //     'description' => 'Research',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'consignment_purpose',
            //     'cate_code' => '3',
            //     'description' => 'Planting/Production (Planting Material)',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'consignment_purpose',
            //     'cate_code' => '4',
            //     'description' => 'Transit/Re-export',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
           
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'KG',
                'description' => 'Kilogram',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'G',
                'description' => 'Gram',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'TON',
                'description' => 'Metric Ton',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'PCS',
                'description' => 'Pieces',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'BGS',
                'description' => 'Bags',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'LTR',
                'description' => 'Litre',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'unit_measurement',
                'cate_code' => 'ML',
                'description' => 'Millilitre',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '1',
            //     'description' => 'Fresh Fruit',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '2',
            //     'description' => 'Product',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '3',
            //     'description' => 'Fresh Vegetables',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '4',
            //     'description' => 'Planting Material',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '5',
            //     'description' => 'Planting Media',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '6',
            //     'description' => 'Others',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '7',
            //     'description' => 'Woods Product',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '8',
            //     'description' => 'Fertilizer',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            // [
            //     'cate_name' => 'condition_category',
            //     'cate_code' => '9',
            //     'description' => 'Animal Feed',
            //     'is_del' => false,
            //     'created_at' => $now,
            //     'updated_at' => $now,
            // ],
            [
                'cate_name' => 'consignment_category',
                'cate_code' => '1',
                'description' => 'Spices',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'consignment_category',
                'cate_code' => '2',
                'description' => 'Vegetables',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'consignment_category',
                'cate_code' => '3',
                'description' => 'Fruits',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cate_name' => 'consignment_category',
                'cate_code' => '4',
                'description' => 'Roots',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
            
        ]);
    }
}
