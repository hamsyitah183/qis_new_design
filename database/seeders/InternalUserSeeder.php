<?php

namespace Database\Seeders;

use App\Models\InternalUser;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InternalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🔹 Admin
        $admin = InternalUser::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'uuid' => Str::uuid(),
                'fullname' => 'System Admin',
                //'username' => 'admin',
                'email' => 'admin@example.com',
                'phone' => '0130000001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '900101010001',
                'password' => Hash::make('password123'),
                
            ]
        );
        $admin->assignRole('admin');

        $admin2 = InternalUser::firstOrCreate(
            ['email' => 'hamsyitahnur@gmail.com'],
            [
                'uuid' => Str::uuid(),
                'fullname' => 'Hamsyitah Internal',
                //'username' => 'admin',
                'email' => 'hamsyitahnur@gmail.com',
                'phone' => '0130400001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '000101010001',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now()
            ]
        );
        $admin2->assignRole('admin');

        // 🔹 Officer
        $officer = InternalUser::firstOrCreate(
            ['email' => 'officer@example.com'],
            [
                'uuid' => Str::uuid(),
                'fullname' => 'Department Officer',
                //'username' => 'officer',
                'email' => 'officer@example.com',
                'phone' => '0130000002',
                'position' => 'Officer',
                'office' => 'District Office',
                'no_ic' => '900101010002',
                'password' => Hash::make('password123'),
            ]
        );
        $officer->assignRole('officer');

        // 🔹 Clerk
        $clerk = InternalUser::firstOrCreate(
            ['email' => 'clerk@example.com'],
            [
                'uuid' => Str::uuid(),
                'fullname' => 'Department Clerk',
                //'username' => 'clerk',
                'email' => 'clerk@example.com',
                'phone' => '0130000003',
                'position' => 'Clerk',
                'office' => 'District Office',
                'no_ic' => '900101010003',
                'password' => Hash::make('password123'),
            ]
        );
        $clerk->assignRole('clerk');
    }
}
