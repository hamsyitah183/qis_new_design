<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExporterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('exporter')->insert([
            [
                'name' => 'Sabah Agro Export Sdn Bhd',
                'phone_no' => '+6012-3456789',
                'address' => 'Lot 123, Jalan Kolam, Kota Damansara, Kuala Lumpur',
                'country' => 'SMY',
                'registered_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tropika Fruits Trading',
                'phone_no' => '+6013-9988776',
                'address' => 'Taman Megah, Johor Baharu, Johor',
                'country' => 'SMY',
                'registered_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'No Country Item',
                'phone_no' => '+6013-9988776',
                'address' => 'Taman Megah, Wuuhaa, Wuhuuu',
                'country' => 'TZ',
                'registered_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
