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
    }
}
