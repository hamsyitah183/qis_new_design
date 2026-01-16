<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryNoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now();

        $countries = [
            // Southeast Asia
            ['country' => 'Malaysia', 'start_no' => '+60'],
            ['country' => 'Singapore', 'start_no' => '+65'],
            ['country' => 'Indonesia', 'start_no' => '+62'],
            ['country' => 'Thailand', 'start_no' => '+66'],
            ['country' => 'Philippines', 'start_no' => '+63'],
            ['country' => 'Vietnam', 'start_no' => '+84'],
            ['country' => 'Brunei', 'start_no' => '+673'],

            // Asia
            ['country' => 'China', 'start_no' => '+86'],
            ['country' => 'Japan', 'start_no' => '+81'],
            ['country' => 'South Korea', 'start_no' => '+82'],
            ['country' => 'India', 'start_no' => '+91'],
            ['country' => 'Pakistan', 'start_no' => '+92'],

            // Middle East
            ['country' => 'Egypt', 'start_no' => '+20'],
            ['country' => 'Saudi Arabia', 'start_no' => '+966'],
            ['country' => 'United Arab Emirates', 'start_no' => '+971'],
            ['country' => 'Qatar', 'start_no' => '+974'],

            // Others
            ['country' => 'United Kingdom', 'start_no' => '+44'],
            ['country' => 'United States', 'start_no' => '+1'],
            ['country' => 'Australia', 'start_no' => '+61'],
            ['country' => 'Germany', 'start_no' => '+49'],
            ['country' => 'France', 'start_no' => '+33'],
        ];

        // Add timestamps to each row
        $data = array_map(function ($item) use ($now) {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $countries);

        DB::table('country_no_phones')->insert($data);
    }
}
