<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaInformation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AzureBlobStorageService
{
    protected $accountName;
    protected $accountKey;
    protected $container;
    protected $storageUrl;

    public function __construct()
    {
        $this->accountName = config('filesystems.disks.azure.name');
        $this->accountKey = config('filesystems.disks.azure.key');
        $this->container = config('filesystems.disks.azure.container');
        $this->storageUrl = config('filesystems.disks.azure.url');
    }

    /**
     * Upload a file to Azure Blob Storage and save media information.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $entityId
     * @param string $entityType
     * @param string $mediaType
     * @return Media
     */
    public function uploadFileWithMetadata($file, $entityId, $entityType, $mediaType)
    {
        // Define default storage paths for different entity types
        $storagePaths = [
            'post' => 'post/',
            'player' => 'player/',
            'event' => 'event/',
            'blog' => 'blog/',
            'school' => 'school/',
            'business' => 'business/'
        ];
    
        // Set the base blob name and storage path without entityId
        $blobName = isset($storagePaths[$entityType]) ? $storagePaths[$entityType] : 'general/';
        $storagePath = $blobName; // Base path without entityId
    
        // Generate the full file path (including entityId) for actual file storage
        $fileStoragePath = $blobName . $entityId;  // This is the actual file storage path
    
        // Get or create media information with the base storage path
        $mediaInfo = MediaInformation::firstOrCreate([
            'storage_provider' => 'azure',
            'container_name' => $this->container,
            'blob_name' => $blobName,  // Base path without entityId
            'media_type' => $mediaType,
            'base_url' => $this->storageUrl,
        ], [
            'storage_path' => $storagePath  // Store the base storage path in storage_path
        ]);
    
        // Generate a random 20-character file name with the original file extension
        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
    
        // Upload the file using the full path generated above
        $fileUrl = $this->uploadFile($file, "{$fileStoragePath}/{$fileName}");
    
        // Save media record in the database
        $media = Media::create([
            'id' => (string) Str::uuid(),
            'media_information_id' => $mediaInfo->id,
            'entity_id' => $entityId,  // The ID of the entity (post, user, etc.)
            'entity_type' => $entityType,  // e.g., post, profile, business, etc.
            'file_name' => $fileName,
            'file_url' => $fileUrl,  // Full URL of the uploaded file
        ]);
    
        return $media;
    }
    

    /**
     * Upload a file to Azure Blob Storage.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @return string URL of the uploaded file
     */
    public function uploadFile($file, $path)
    {
        // Remove any extra slashes from the path and construct the full blob name
        $blobName = ltrim($path, '/'); // Make sure there's no leading slash in the path
        $url = rtrim($this->storageUrl, '/') .'/'.  ltrim($this->container, '/') . '/' . $blobName;
    
        // Prepare the request headers
        $date = gmdate('D, d M Y H:i:s T', time());
        $contentLength = $file->getSize();
        $mimeType = $file->getMimeType();
    
        // Get file contents (binary data)
        $fileContents = file_get_contents($file->getRealPath());
    
        // Construct the canonical headers and canonical resource
        $canonicalHeaders = "x-ms-blob-type:BlockBlob\nx-ms-date:{$date}\nx-ms-version:2019-12-12";
        $canonicalResource = "/{$this->accountName}/{$this->container}/{$blobName}";
    
        // Construct the string to sign
        $stringToSign = "PUT\n" .
            "\n" .    // Content-Encoding (empty)
            "\n" .    // Content-Language (empty)
            "{$contentLength}\n" .   // Content-Length
            "\n" .    // Content-MD5 (empty)
            "{$mimeType}\n" .  // Content-Type
            "\n" .    // Date (empty because we're using x-ms-date)
            "\n" .    // If-Modified-Since (empty)
            "\n" .    // If-Match (empty)
            "\n" .    // If-None-Match (empty)
            "\n" .    // If-Unmodified-Since (empty)
            "\n" .    // Range (empty)
            "{$canonicalHeaders}\n" . // CanonicalizedHeaders
            "{$canonicalResource}";   // CanonicalizedResource
    
        // Generate the signature
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true));
    
        $authorizationHeader = "SharedKey {$this->accountName}:{$signature}";
    
        // Make the HTTP request to Azure Storage using `withBody()` to send raw file contents
        $response = Http::withHeaders([
            'Authorization' => $authorizationHeader,
            'x-ms-blob-type' => 'BlockBlob',
            'x-ms-date' => $date,
            'x-ms-version' => '2019-12-12',
            'Content-Type' => $mimeType,
            'Content-Length' => $contentLength,
        ])->withBody($fileContents, $mimeType)  // Explicitly send raw binary data
            ->put($url);
    
        if ($response->successful()) {
            return $url; // Return the URL of the uploaded file without any trailing slash
        } else {
            throw new \Exception('Error uploading file to Azure: ' . $response->body());
        }
    }
    

    /**
     * Retrieve media related to a specific entity (e.g., post, user, business).
     * 
     * @param string $entityId The ID of the entity (e.g., post ID, user ID).
     * @param string $entityType The type of entity (e.g., 'post', 'user', 'business').
     * @return \Illuminate\Database\Eloquent\Collection Media related to the entity
     */
    public function getMediaByEntity($entityId, $entityType)
    {
        // Retrieve all media related to the entity by entity ID and entity type
        return Media::with('mediaInformation')
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->get();
    }
}
