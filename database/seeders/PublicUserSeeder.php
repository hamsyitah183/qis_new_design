<?php

namespace Database\Seeders;

use App\Models\PublicUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PublicUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        PublicUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'fullname' => 'Nur Hamsyitah',
            'no_ic' => '900101-12-3456', // you can change this
            'email' => 'hamsyitahnur@gmail.com',
            'account_type' => 'individu',
            'phone_number' => '0123456789',
            'office_number' => null,
            'address_1' => 'Kg. Example Address',
            'address_2' => null,
            'postcode' => '89657',
            'district' => 'Tambunan',
            'state' => 'Sabah',
            'password' => Hash::make('password123'), // change this if needed
            'doa_verified' => 0,
            'verification_attachment' => null,
            'email_verified_at' => null,
        ]);

        if ($public = PublicUser::where('email', 'hamsyitahnur@example.com')->first()) {
            $public->assignRole('public');
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
