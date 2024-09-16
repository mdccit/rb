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
        // Get or create media information
        $mediaInfo = MediaInformation::firstOrCreate([
            'storage_provider' => 'azure',
            'container_name' => $this->container,
            'blob_name' => 'media', // Path inside the blob storage
            'media_type' => $mediaType,
            'base_url' => $this->storageUrl,
        ]);
    
        // Upload the file using the existing uploadFile() method
        $fileUrl = $this->uploadFile($file, $mediaInfo->blob_name);

        if ($fileUrl) {
          Log::info("File uploaded successfully to: $fileUrl");
      } else {
          Log::error("File upload failed");
          return;
      }
    
        // Generate a unique file name (optional if not needed)
        $fileName = uniqid() . '_' . $file->getClientOriginalName();
    
        // Save media record in the database
        $media = Media::create([
            'id' => (string) Str::uuid(),
            'media_information_id' => $mediaInfo->id,
            'entity_id' => $entityId, // The ID of the entity (post, user, etc.)
            'entity_type' => $entityType, // e.g., post, profile, business, etc.
            'file_name' => $fileName,
            'file_url' => $fileUrl, // Full URL of the uploaded file
        ]);

        if ($media) {
          Log::info("Media record created successfully for file: $fileName");
      } else {
          Log::error("Failed to create media record for file: $fileName");
      }
    
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
        $blobName = ltrim($path . '/' . $file->getClientOriginalName(), '/');
        $url = rtrim($this->storageUrl, '/') . '/' . ltrim($this->container, '/') . '/' . $blobName;

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
            return $url; // Return the URL of the uploaded file
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
