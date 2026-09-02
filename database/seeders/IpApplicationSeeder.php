<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IpApplication;
use App\Models\PublicUser;
use App\Models\IpEntryPoint;
use Carbon\Carbon;

class IpApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the specific user
        $user = PublicUser::where('email', 'hamsyitahnur@gmail.com')->first();
        if (!$user) {
            $user = PublicUser::first();
        }
        
        $entryPoint = IpEntryPoint::first();

        $exporter = \App\Models\Exporter::first();

        $applications = [
            ['application_id' => 'IPO2608277241', 'status' => 'Completed'],
            ['application_id' => 'IPO2608288004', 'status' => 'Completed'],
            ['application_id' => 'IPO2608283944', 'status' => 'Completed'],
            ['application_id' => 'IPO2609012724', 'status' => 'Clerk review in-progress'],
        ];

        foreach ($applications as $app) {
            IpApplication::updateOrCreate(
                ['application_id' => $app['application_id']],
                [
                    'eta' => Carbon::now()->addDays(5),
                    'transport_type' => 'Air',
                    'entry_point' => $entryPoint ? $entryPoint->id : null,
                    'user_id' => $user ? $user->uuid : null,
                    'importer_id' => $user ? $user->uuid : null,
                    'exporter_id' => $exporter ? $exporter->id : 1,
                    'importer_detail' => json_encode(['name' => $user ? $user->name : 'Importer', 'email' => $user ? $user->email : '']),
                    'category_application' => 1,
                    'importer_verify' => 'Yes',
                    'date_importer_verify' => Carbon::now(),
                    'status' => $app['status'],
                ]
            );
        }
    }
}
