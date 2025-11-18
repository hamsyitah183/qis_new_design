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
        // Make sure roles exist first
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

        // Users data
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
            ],[
                'email' => 'aarondalejchin@gmail.com',
                'fullname' => 'Aaron Internal',
                'phone_number' => '0198227530',
                'position' => 'Administrator',
                'office' => 'HQ',
                'no_ic' => '911117126657',
                'password' => '12345678',
                'email_verified_at' => Carbon::now(),
                'role' => 'admin',
            ]
        ];

        foreach ($users as $data) {
            // Check if user exists
            $user = InternalUser::updateOrCreate(
                ['email' => $data['email']], // search criteria
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

            // Assign role safely
            if (!$user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
