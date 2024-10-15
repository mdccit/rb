<?php


namespace App\Traits;


use App\Models\Media;
use App\Models\MediaInformation;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait AzureBlobStorage
{
    /**
     * Support Reference : https://github.com/matthewbdaly/laravel-azure-storage
     */


    /**
     * Upload Single File.
     *
     * @param File $file The file object need to upload.
     * @param string $entityId The file upload for which model unique id.
     * @param string $entityType The type for differentiate the storage locations.
     * @return string the url of uploaded file.
     */
    public function uploadSingleFile($file, $entityId, $entityType)
    {
        $data = null;
        $mediaInfo = MediaInformation::connect(config('database.secondary'))->where('blob_name', $entityType)->first();
        if($mediaInfo){
            $storagePath = $mediaInfo->storage_path;

            $fileName = uniqid() . Str::random(20) . time() . '.' . $file->getClientOriginalExtension();
            $contentType = $file->getClientMimeType();
            $options = ['Content-Type' => $contentType];
            $upload_success = Storage::disk('azure')->putFileAs($storagePath, $file, $fileName, $options);
            if ($upload_success){
                // Save media record in the database
                $media = Media::connect(config('database.default'))->create([
                    'id' => (string) Str::uuid(),
                    'media_information_id' => $mediaInfo->id,
                    'entity_id' => $entityId,  // The ID of the entity (post, user, etc.)
                    'media_type' => $this->getMimeMediaType($file),
                    'entity_type' => $entityType,  // e.g., post, profile, business, etc.
                    'file_name' => $fileName,
                ]);

                $data = [
                    'media_id' => $media->id,
                    'url' => Storage::disk('azure')->url("$storagePath$fileName"),//URL = base_url / container_name / storage_path / entity_id (user_id) / file_name
                    'media_type' => $media->media_type
                ];
            }

        }

        return $data;
    }

    /**
     * Upload Multiple Files.
     *
     * @param File[] $files The file object need to upload.
     * @param string $entityId The file upload for which model unique id.
     * @param string $entityType The type for differentiate the storage locations.
     * @return array the url of uploaded file.
     */
    public function uploadMultipleFiles($files, $entityId, $entityType)
    {
        $dataArray = array();
        foreach ($files as $file){
            $data = $this->uploadSingleFile($file, $entityId, $entityType);
            if($data){
                array_push($dataArray,$data);
            }
        }

        return $dataArray;
    }

    /**
     * Remove File.
     *
     * @param string $mediaId The media id which was recorded in db
     * @return string the is removed or not.
     */
    public function removeFile($mediaId)
    {
        $media = Media::connect(config('database.default'))
            ->join('media_information', 'media_information.id', '=', 'media.media_information_id')
            ->where('media.id', $mediaId)
            ->select(
                'media.id',
                'media.file_name',
                'media_information.storage_path',
                )
            ->first();
        $isRemoved = false;
        if($media) {
            Storage::disk('azure')->delete("$media->storage_path$media->file_name");
            $media->delete();

            $isRemoved = true;
        }

        return $isRemoved;
    }

    /**
     * Get Single File by Media Id.
     *
     * @param string $mediaId The media id which was recorded in db
     * @return string the url of uploaded file
     */
    public function getFileByMediaId($mediaId)
    {
        $media = Media::connect(config('database.secondary'))
            ->join('media_information', 'media_information.id', '=', 'media.media_information_id')
            ->where('media.id', $mediaId)
            ->select(
                'media.id',
                'media.file_name',
                'media_information.storage_path',
                'media.media_type',
                )
            ->first();
        $data = null;
        if($media){
            $data = [
                'media_id' => $media->id,
                'url' => Storage::disk('azure')->url("$media->storage_path$media->file_name"),//URL = base_url / container_name / storage_path / entity_id (user_id) / file_name
                'media_type' => $media->media_type
            ];
        }

        return $data;
    }

    /**
     * Get Single File by Entity Id.
     *
     * @param string $entityId The file upload for which model unique id.
     * @param string $entityType The type for differentiate the storage locations.
     * @return string the url of uploaded file.
     */
    public function getSingleFileByEntityId($entityId, $entityType)
    {
        $media = Media::connect(config('database.secondary'))
            ->join('media_information', 'media_information.id', '=', 'media.media_information_id')
            ->where('media.entity_id', $entityId)
            ->where('media.entity_type', $entityType)
            ->select(
                'media.id',
                'media.file_name',
                'media_information.storage_path',
                'media.media_type',
                )
            ->orderBy('media.created_at', 'desc')
            ->first();
        $data = null;
        if($media){
            error_log('$media->storage_path : '.$media->storage_path);
            error_log('$media->file_name : '.$media->file_name);
            $data = [
                'media_id' => $media->id,
                'url' => Storage::disk('azure')->url($media->storage_path.$media->file_name),//URL = base_url / container_name / storage_path / entity_id (user_id) / file_name
                'media_type' => $media->media_type
            ];
        }

        return $data;
    }

    /**
     * Get Multiple Files by Entity Id.
     *
     * @param string $entityId The file upload for which model unique id.
     * @param string $entityType The type for differentiate the storage locations.
     * @return array the url of uploaded file.
     */
    public function getMultipleFilesByEntityId($entityId, $entityType)
    {
        $media_list = Media::connect(config('database.secondary'))
            ->join('media_information', 'media_information.id', '=', 'media.media_information_id')
            ->where('media.entity_id', $entityId)
            ->where('media.entity_type', $entityType)
            ->select(
                'media.id',
                'media.file_name',
                'media_information.storage_path',
                'media.media_type',
                )
            ->orderBy('media.created_at', 'desc')
            ->get();
        $dataArray = array();
        if($media_list){
            foreach ($media_list as $media) {
                $data = [
                    'media_id' => $media->id,
                    'url' => Storage::disk('azure')->url($media->storage_path.$media->file_name),//URL = base_url / container_name / storage_path / entity_id (user_id) / file_name
                    'media_type' => $media->media_type
                ];

                array_push($dataArray,$data);
            }
        }

        return $dataArray;
    }

    public function getMimeMediaType($file)
    {
        $mimeType = $file->getMimeType();

        // Check if the file is an image
        if (strpos($mimeType, 'image') !== false) {
            return 'image';
        }

        // Check if the file is a video
        if (strpos($mimeType, 'video') !== false) {
            return 'video';
        }

        // Default media type if not image or video (optional)
        return 'unknown';
    }
}
