<?php

namespace Database\Seeders;

use App\Models\InternalUser;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InternalUserSeeder extends Seeder
{
    public function run(): void
    {
        // ==================================================
        // 1️⃣ Ensure roles exist first
        // ==================================================
        $roles = [
            'admin' => 'internal',
            'officer' => 'internal',
            'clerk' => 'internal',
        ];

        foreach ($roles as $roleName => $guard) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        // ==================================================
        // 2️⃣ Users data
        // ==================================================
        $users = [
            [
                'email' => 'admin@example.com',
                'fullname' => 'System Admin',
                'phone_number' => '0130000001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '900101010001',
                'password' => 'password123',
                'role' => 'admin',
            ],
            [
                'email' => 'hamsyitahnur@gmail.com',
                'fullname' => 'Hamsyitah Internal',
                'phone_number' => '0130400001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '000101010001',
                'password' => 'password123',
                'email_verified_at' => Carbon::now(),
                'role' => 'admin',
            ],
            [
                'email' => 'officer@example.com',
                'fullname' => 'Department Officer',
                'phone_number' => '0130000002',
                'position' => 'Officer',
                'office' => 'District Office',
                'no_ic' => '900101010002',
                'password' => 'password123',
                'email_verified_at' => Carbon::now(),
                'role' => 'officer',
            ],
            [
                'email' => 'clerk@example.com',
                'fullname' => 'Department Clerk',
                'phone_number' => '0130000003',
                'position' => 'Clerk',
                'office' => 'District Office',
                'no_ic' => '900101010003',
                'password' => 'password123',
                'role' => 'clerk',
            ],
        ];

        // ==================================================
        // 3️⃣ Seed users safely
        // ==================================================
        foreach ($users as $data) {
            // Create or update the user
            $user = InternalUser::updateOrCreate(
                ['email' => $data['email']], // search by email
                [
                    'uuid' => Str::uuid(), // make sure UUID is always set
                    'fullname' => $data['fullname'],
                    'phone_number' => $data['phone_number'],
                    'position' => $data['position'],
                    'office' => $data['office'],
                    'no_ic' => $data['no_ic'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => $data['email_verified_at'] ?? null,
                ]
            );

            // Assign role if not already assigned
            if (!$user->hasRole($data['role'], 'internal')) {
                $user->assignRole($data['role']); // internal guard
            }
        }
    }
}
