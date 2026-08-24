<?php

namespace Database\Seeders;

use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

class DocumentRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentRequirement::updateOrCreate(
            [
                'module' => 'user',
                'name' => 'Identification Documents (IC / Passport)',
            ],
            [
                'description' => 'Dokumen Pengenalan Diri (Kad Pengenalan / Pasport)',
                'is_required' => true,
                'requires_expiry' => false,
                'is_active' => true,
            ]
        );


        DocumentRequirement::updateOrCreate(
            [
                'module' => 'user',
                'name' => 'Business Registration Certificate (SSM / Trading License)',
            ],
            [
                'description' => 'Sijil Pendaftaran Perniagaan (SSM / Lesen Berniaga)',
                'is_required' => true,
                'requires_expiry' => true,
                'is_active' => true,
            ]
        );
    }
}