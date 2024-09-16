<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
     * Upload a file to Azure Blob Storage
     * 
     * @param $file UploadedFile The file to upload
     * @param string $path The path in the container where the file will be stored
     * @return string URL of the uploaded file
     */
    public function uploadFile(\Illuminate\Http\UploadedFile $file, string $path): string
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
}
