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
            'user_profile_picture' => 'user/profile/picture/',
            'user_profile_cover' => 'user/profile/cover/',
            'user_profile_media' => 'user/profile/media/',

            'school_profile_picture' => 'school/profile/picture/',
            'school_profile_cover' => 'school/profile/cover/',
            'school_profile_media' => 'school/profile/media/',

            'business_profile_picture' => 'business/profile/picture/',
            'business_profile_cover' => 'business/profile/cover/',
            'business_profile_media' => 'business/profile/media/',

            'transcript' => 'user/profile/transcript/',
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
