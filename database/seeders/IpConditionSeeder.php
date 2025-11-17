<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('ip_condition')->insert([
            // category: 1-Fresh Fruit, 2-Product, 3-Fresh Vegetables, 4-Planting Material, 5-Planting Media, 6-Others,7-Woods Product,8-Fertilizer,9-Animal Feed
            [
                'category' => 1, 
                'item_name' => 'CORN',
                'addional_condition' => '<pre>7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.</pre>',
                'quantity_limit' => null,
                'date_limit' => null,
                'country' => json_encode(['SMY','CN','IN','PK']), 
                'usage' => json_encode(['Fresh Produce', 'For Animal Consumption']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'DURIAN',
                'addional_condition' => '<pre>7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.</pre>',
                'quantity_limit' => null,
                'date_limit' => null,
                'country' => json_encode(['SWK','SMY']),
                'usage' => json_encode(['Fresh Produce']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'POKOK DURIAN',
                'addional_condition' => '<pre>"7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration: 
1. All the plants are to be cleansed of any soil.
2. The plants must be healthy and taken from an accredited farm by planting material verification scheme Department of Agriculture (DOA) of the exporting country.
3. All plants are to be dipped in 0.2% Malathion 80 E.C. + 0.4% Thiram + 0.3% Nemacur for 5 minutes (or any suitable insecticide, fungicide and Nematicide) prior to shipment.
4. Subject to quarantine inspection upon arrival in Kota Kinabalu."
</pre>',
                'quantity_limit' => null,
                'date_limit' => null,
                'country' => json_encode(['SNP']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
