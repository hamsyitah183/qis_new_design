<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            'Kota Kinabalu',
            'Kudat',
            'Sandakan',
            'Lahad Datu',
            'Tawau',
            'Kunak',
            'Semporna',
            'Kuala Penyu',
            'Sipitang',
        ];

        // Add HQ to the list
        $branches[] = 'HQ';

        // Seed branches
        foreach ($branches as $branchName) {
            Branch::updateOrCreate(
                ['name' => $branchName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Branches seeded successfully.');
    }
}