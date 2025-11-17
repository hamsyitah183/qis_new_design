<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('country')->insert([
            ['code' => 'AE', 'name' => 'United Arab Emirates'],
            ['code' => 'AR', 'name' => 'Argentina'],
            ['code' => 'AT', 'name' => 'Austria'],
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'BE', 'name' => 'Belgium'],
            ['code' => 'BN', 'name' => 'Brunei'],
            ['code' => 'BO', 'name' => 'Bolivia'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'CH', 'name' => 'Switzerland'],
            ['code' => 'CL', 'name' => 'Chile'],
            ['code' => 'CM', 'name' => 'Cameroon'],
            ['code' => 'CN', 'name' => 'China'],
            ['code' => 'CZ', 'name' => 'Czechia (Czech Republic)'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'DK', 'name' => 'Denmark'],
            ['code' => 'EE', 'name' => 'Estonia'],
            ['code' => 'EG', 'name' => 'Egypt'],
            ['code' => 'ES', 'name' => 'Spain'],
            ['code' => 'FI', 'name' => 'Finland'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'GA', 'name' => 'Gabon'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'HK', 'name' => 'Hong Kong'],
            ['code' => 'HU', 'name' => 'Hungary'],
            ['code' => 'ID', 'name' => 'Indonesia'],
            ['code' => 'IN', 'name' => 'India'],
            ['code' => 'IT', 'name' => 'Italy'],
            ['code' => 'JO', 'name' => 'Jordan'],
            ['code' => 'JP', 'name' => 'Japan'],
            ['code' => 'KE', 'name' => 'Kenya'],
            ['code' => 'KR', 'name' => 'South Korea (Republic of Korea)'],
            ['code' => 'LA', 'name' => 'Laos'],
            ['code' => 'LK', 'name' => 'Sri Lanka'],
            ['code' => 'LT', 'name' => 'Lithuania'],
            ['code' => 'LV', 'name' => 'Latvia'],
            ['code' => 'MM', 'name' => 'Myanmar'],
            ['code' => 'MW', 'name' => 'Malawi'],
            ['code' => 'MX', 'name' => 'Mexico'],
            ['code' => 'MY', 'name' => 'Malaysia'],
            ['code' => 'NL', 'name' => 'Netherlands'],
            ['code' => 'NZ', 'name' => 'New Zealand'],
            ['code' => 'PE', 'name' => 'Peru'],
            ['code' => 'PG', 'name' => 'Papua New Guinea'],
            ['code' => 'PH', 'name' => 'Philippines'],
            ['code' => 'PK', 'name' => 'Pakistan'],
            ['code' => 'PL', 'name' => 'Poland'],
            ['code' => 'PY', 'name' => 'Paraguay'],
            ['code' => 'RO', 'name' => 'Romania'],
            ['code' => 'RU', 'name' => 'Russian Federation'],
            ['code' => 'SE', 'name' => 'Sweden'],
            ['code' => 'SG', 'name' => 'Singapore'],
            ['code' => 'SMY', 'name' => 'Peninsular Malaysia (Semenanjung Malaysia)'],
            ['code' => 'SWK', 'name' => 'Sarawak, Malaysia'],
            ['code' => 'TH', 'name' => 'Thailand'],
            ['code' => 'TR', 'name' => 'Turkey'],
            ['code' => 'TW', 'name' => 'Taiwan'],
            ['code' => 'TZ', 'name' => 'Tanzania'],
            ['code' => 'UA', 'name' => 'Ukraine'],
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'UZ', 'name' => 'Uzbekistan'],
            ['code' => 'VN', 'name' => 'Vietnam'],
            ['code' => 'ZA', 'name' => 'South Africa'],
            ['code' => 'SNP', 'name' => 'All Registered Country Only'],
        ]);
    }
}
