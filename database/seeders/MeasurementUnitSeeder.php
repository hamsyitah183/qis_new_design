<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeasurementUnitSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get all unit_measurement from public_code
        $units = DB::table('public_code')
            ->where('cate_name', 'unit_measurement')
            ->get();

        foreach ($units as $unit) {

            $conversion = null;

            switch ($unit->cate_code) {
                case 'KG':
                    $conversion = 1; // 1 KG = 1 KG
                    break;

                case 'G':
                    $conversion = 0.001; // 1 Gram = 0.001 KG
                    break;

                case 'TON':
                    $conversion = 1000; // 1 TON = 1000 KG
                    break;

                case 'ML':
                    $conversion = 0.001; // assume water density (1L = 1KG)
                    break;

                case 'LTR':
                    $conversion = 1; // assume water density (1L = 1KG)
                    break;

                case 'PCS':
                case 'BGS':
                    $conversion = null; // cannot auto convert
                    break;
            }

            DB::table('measurement_units')->insert([
                'measurement_id' => $unit->id,
                'conversion'     => $conversion,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}