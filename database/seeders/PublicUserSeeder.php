<?php

namespace Database\Seeders;

use App\Models\PublicUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PublicUserSeeder extends Seeder
{
    public function run(): void
    {
        $public = PublicUser::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
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
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Assign public role
        $public->assignRole('public');
    }
}
