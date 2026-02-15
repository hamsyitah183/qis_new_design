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
            // 'superadmin' => 'internal',
            'admin' => 'internal',
            'officer' => 'internal',
            'clerk' => 'internal',
            'finance' => 'internal',
            // 'clerk' => 'internal',
        ];

        foreach ($roles as $roleName => $guard) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        // ==================================================
        // 2️⃣ Default users
        // ==================================================
        $users = [
            [
                'email' => 'admin@example.com',
                'fullname' => 'System Admin',
                'phone_number' => '+60130000001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '900101010001',
                'password' => 'password123',
                'role' => 'superadmin',
                'email_verified_at' => Carbon::now(),
            ],
            [
                'email' => 'hamsyitahnur@gmail.com',
                'fullname' => 'Hamsyitah Internal',
                'phone_number' => '+60130400001',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '000101010001',
                'password' => 'password123',
                'role' => 'superadmin',
                'email_verified_at' => Carbon::now(),
            ],
            [
                'email' => 'officer@example.com',
                'fullname' => 'Department Officer',
                'phone_number' => '+60130000002',
                'position' => 'Officer',
                'office' => 'District Office',
                'no_ic' => '900101010002',
                'password' => 'password123',
                'role' => 'officer',
                'email_verified_at' => Carbon::now(),
            ],
            [
                'email' => 'clerk@example.com',
                'fullname' => 'Department Clerk',
                'phone_number' => '+60130000003',
                'position' => 'Clerk',
                'office' => 'District Office',
                'no_ic' => '900101010003',
                'password' => 'password123',
                'role' => 'clerk',
                'email_verified_at' => Carbon::now(),
            ],[
                'email' => 'aarondalejchin@gmail.com',
                'fullname' => 'Aaron Internal',
                'phone_number' => '+60198227530',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '911117126657',
                'password' => '12345678',
                'email_verified_at' => Carbon::now(),
                'role' => 'admin',
            ]
            ,[
                'email' => 'finance@example.com',
                'fullname' => 'Finance',
                'phone_number' => '+601798227530',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '9111171266957',
                'password' => '12345678',
                'email_verified_at' => Carbon::now(),
                'role' => 'finance',
            ]
        ];

        // ==================================================
        // 3️⃣ Add 30 more internal users (randomized)
        // ==================================================
        for ($i = 1; $i <= 3; $i++) {
            $role = ['admin', 'officer', 'clerk'][array_rand(['admin','officer','clerk'])];
            $users[] = [
                'email' => "internal{$i}@example.com",
                'fullname' => "Internal User {$i}",
                'phone_number' => '+6013' . str_pad(rand(1000004, 9999999), 7, '0', STR_PAD_LEFT),
                'position' => ucfirst($role),
                'office' => 'HQ',
                'no_ic' => str_pad(rand(900101010004, 900101019999), 12, '0', STR_PAD_LEFT),
                'password' => 'password123',
                'role' => $role,
                'email_verified_at' => Carbon::now(),
            ];
        }

        // ==================================================
        // 4️⃣ Seed users safely
        // ==================================================
        foreach ($users as $data) {
            $user = InternalUser::updateOrCreate(
                ['email' => $data['email']],
                [
                    'uuid' => Str::uuid(),
                    'fullname' => $data['fullname'],
                    'phone_number' => $data['phone_number'],
                    'position' => $data['position'],
                    'office' => $data['office'],
                    'no_ic' => $data['no_ic'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => $data['email_verified_at'] ?? null,
                ]
            );

            if (!$user->hasRole($data['role'], 'internal')) {
                $user->assignRole($data['role']);
            }
        }
    }
}
