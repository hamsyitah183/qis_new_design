<?php

namespace Database\Seeders;

use App\Models\PublicUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicUserSeeder extends Seeder
{
    public function run(): void
    {
        // ==================================================
        // 1️⃣ Default public user
        // ==================================================
        $users = [
            [
                'fullname' => 'Nur Hamsyitah',
                'no_ic' => '900101123456',
                'email' => 'hamsyitahnur@gmail.com',
                'account_type' => 'individu',
                'phone_number' => '0123456789',
                'office_number' => null,
                'address_1' => 'Kg. Example Address',
                'address_2' => null,
                'postcode' => '89657',
                'district' => 'Tambunan',
                'state' => 'Sabah',
                'doa_verified' => 1
            ],
        ];

        // ==================================================
        // 2️⃣ Generate 100 additional public users
        // ==================================================
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'fullname' => "Public User {$i}",
                'no_ic' => '900101' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'email' => "public{$i}@example.com",
                'account_type' => 'individu',
                'phone_number' => '012' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'office_number' => null,
                'address_1' => "Kg. Public Address {$i}",
                'address_2' => null,
                'postcode' => str_pad(rand(80000, 89999), 5, '0', STR_PAD_LEFT),
                'district' => 'Tambunan',
                'state' => 'Sabah',
                'doa_verified' => 1
            ];
        }

        // ==================================================
        // 3️⃣ Seed users
        // ==================================================
        foreach ($users as $data) {
            $user = PublicUser::updateOrCreate(
                ['email' => $data['email']], // ensure no duplicates
                [
                    'uuid' => Str::uuid(),
                    'fullname' => $data['fullname'],
                    'no_ic' => $data['no_ic'],
                    'account_type' => $data['account_type'],
                    'phone_number' => $data['phone_number'],
                    'office_number' => $data['office_number'],
                    'address_1' => $data['address_1'],
                    'address_2' => $data['address_2'],
                    'postcode' => $data['postcode'],
                    'district' => $data['district'],
                    'state' => $data['state'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign public role
            if (!$user->hasRole('public')) {
                $user->assignRole('public');
            }
        }

        $user2 = PublicUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'fullname' => 'Aaron Chin',
            'no_ic' => '911117-12-6557',
            'email' => 'aarondalejchin@gmail.com',
            'account_type' => 'individu',
            'phone_number' => '0198887777',
            'office_number' => '087-456789',
            'address_1' => 'Lot 45, Jalan Tun Fuad Stephens',
            'address_2' => 'Kota Kinabalu Industrial Area',
            'postcode' => '88000',
            'district' => 'Kota Kinabalu',
            'state' => 'Sabah',
            'password' => Hash::make('12345678'),
            'doa_verified' => 0,
            'verification_attachment' => null,
            'email_verified_at' => now(),
        ]);

        $user2->assignRole('public');

        $user3 = PublicUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'fullname' => 'Company',
            'no_ic' => '0178606030',
            'email' => 'croptonic@gmail.com',
            'account_type' => 'company',
            'phone_number' => '0198227530',
            'office_number' => '087-456789',
            'address_1' => 'Lot 45, Jalan Tun Fuad Stephens',
            'address_2' => 'Kota Kinabalu Industrial Area',
            'postcode' => '88000',
            'district' => 'Kota Kinabalu',
            'state' => 'Sabah',
            'password' => Hash::make('12345678'),
            'doa_verified' => 1,
            'verification_attachment' => null,
            'email_verified_at' => now(),
        ]);

        $user3->assignRole('public');
    }
}
