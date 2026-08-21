<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\InternalUser;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run()
    {
        // Get or create an internal user to be the uploader
        $user = InternalUser::first();
        

        $galleries = [
            [
                'name'        => 'Kota Kinabalu Port checkpoint',
                'path'        => 'images/gallery/kk-port.jpg',
                'description' => 'Kota Kinabalu Port checkpoint',
            ],
            [
                'name'        => 'Quarantine laboratory',
                'path'        => 'images/gallery/quarantine-lab.jpg',
                'description' => 'Quarantine laboratory',
            ],
            [
                'name'        => 'Cargo inspection',
                'path'        => 'images/gallery/cargo-inspection.jpg',
                'description' => 'Cargo inspection',
            ],
            [
                'name'        => 'Agricultural farms across Sabah',
                'path'        => 'images/gallery/agricultural-farm.jpg',
                'description' => 'Agricultural farms across Sabah',
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create([
                'user_id'     => $user->uuid,
                'name'        => $gallery['name'],
                'path'        => $gallery['path'],
                'description' => $gallery['description'],
            ]);
        }
    }
}