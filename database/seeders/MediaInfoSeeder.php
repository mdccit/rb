<?php

namespace Database\Seeders;

use App\Models\MediaInformation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MediaInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storagePaths = [
            'user_profile_media' => 'user/profile/media/',

            'school_profile_picture' => 'school/profile/picture/',
            'school_profile_cover' => 'school/profile/cover/',
            'school_profile_media' => 'school/profile/media/',

            'business_profile_picture' => 'business/profile/picture/',
            'business_profile_cover' => 'business/profile/cover/',
            'business_profile_media' => 'business/profile/media/',

            'transcript' => 'user/profile/transcript/',

            'transfer_user_profile_picture' => 'user/profile/transfer/',
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
