<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
           
            $this->call([
                RoleAndPermissionSeeder::class,
                PublicUserSeeder::class,
                InternalUserSeeder::class,

                IpEntryPointSeeder::class,
                PublicCodeSeeder::class,
                CountrySeeder::class,
                IpConditionSeeder::class,
                ExporterSeeder::class,
            ]);

            DB::commit();
            $this->command->info('✅ Database seeding completed successfully!');
        } catch (Throwable $e) {
            DB::rollBack();

            // Optional: Log error for debugging
            Log::error('❌ Seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Show error message in console
            $this->command->error('❌ Database seeding failed. Rolled back all changes.');
            $this->command->error($e->getMessage());
        }
    }
}
