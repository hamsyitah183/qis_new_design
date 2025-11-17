<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpEntryPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('ip_entry_point')->insert([
            [
                'district' => 1, 
                'entry_name' => 'Kota Kinabalu International Airport (KKIA)',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'MASB Cargo Complex Kota Kinabalu',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'Sepanggar Container Port',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'Bulk Cargo Port Kota Kinabalu',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'Jesselton Point Ferry Terminal, Kota Kinabalu',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'Kota Kinabalu General Post Office',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 1, 
                'entry_name' => 'Post Office DC (Distribution Centre) Kolombong',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 2,
                'entry_name' => 'Ferry Terminal',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 2,
                'entry_name' => 'Kudat Barter Trade Centre',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 3,
                'entry_name' => 'Sandakan Airport',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 3,
                'entry_name' => 'Sandakan Port',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 3,
                'entry_name' => 'Ferry Terminal',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 3,
                'entry_name' => 'Sandakan Barter Trade Centre',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 4,
                'entry_name' => 'Lahad Datu Port',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 4,
                'entry_name' => 'POIC Port, Lahad Datu',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 5,
                'entry_name' => 'Tawau Airport',
                'transport_type' => 'Air',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 5,
                'entry_name' => 'Tawau Port',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 5,
                'entry_name' => 'Ferry Terminal',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 5,
                'entry_name' => 'Tawau Barter Trade Centre',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 6,
                'entry_name' => 'Kunak Port',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 7,
                'entry_name' => 'Ferry Terminal, Semporna',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 8,
                'entry_name' => 'Ferry Terminal, Menumbok',
                'transport_type' => 'Sea',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'district' => 9,
                'entry_name' => 'ICQS Sindumin/Merapok',
                'transport_type' => 'Land',
                'is_del' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
