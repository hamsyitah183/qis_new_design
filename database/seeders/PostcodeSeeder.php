<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\State;
use App\Models\District;
use App\Models\Postcode;

class PostcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/postcodes.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: $jsonPath");
            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (!isset($data['state'])) {
            $this->command->error("Invalid JSON format");
            return;
        }

        foreach ($data['state'] as $stateData) {
            $stateName = $stateData['name'];
            
            // Handle naming discrepancies if any (e.g. Wp Kuala Lumpur vs Kuala Lumpur)
            if ($stateName === "Wp Kuala Lumpur") $stateName = "Kuala Lumpur";
            if ($stateName === "Wp Labuan") $stateName = "Labuan";
            if ($stateName === "Wp Putrajaya") $stateName = "Putrajaya";

            $state = State::where('name', $stateName)->first();

            if (!$state) {
                $this->command->warn("State not found: $stateName");
                continue;
            }

            foreach ($stateData['city'] as $cityData) {
                $districtName = $cityData['name'];
                $postcodes = $cityData['postcode'];

                // In the JSON, 'city' maps to our 'District' model
                $district = District::where('name', $districtName)
                    ->where('state_id', $state->id)
                    ->first();

                if (!$district) {
                    // $this->command->warn("District not found: $districtName in $stateName");
                    continue;
                }

                foreach ($postcodes as $code) {
                    Postcode::firstOrCreate([
                        'district_id' => $district->id,
                        'value' => $code
                    ]);
                }
            }
        }
        
        $this->command->info('Postcodes seeded successfully!');
    }
}
