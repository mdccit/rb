<?php

namespace Database\Seeders;

use App\Models\MediaInformation;
use Illuminate\Database\Seeder;

class MediaInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
              // Define the storage paths for various entities
        $storagePaths = [
            'post' => 'post/',
            'player' => 'player/',
            'event' => 'event/',
            'blog' => 'blog/',
            'school' => 'school/',
            'business' => 'business/',
            'user' => 'user/',
            'user_profile_picture' => 'user/profile/picture',
            'user_profile_cover' => 'user/profile/cover',
        ];

        // Iterate over the storage paths and create the entries in the MediaInformation table
        foreach ($storagePaths as $entityType => $storagePath) {
            MediaInformation::firstOrCreate(
                ['blob_name' => $entityType],  // Condition to check for existing entry
                ['storage_path' => $storagePath] // Create if not exists
            );
        }
    }
}
