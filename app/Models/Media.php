<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Media extends Model
{
    use HasFactory, HasUuids;

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'media';

    // Specify which attributes can be mass-assigned
    protected $fillable = [
        'media_information_id',
        'entity_id',
        'entity_type',
        'media_type',
        'file_name',
        'file_url',
    ];

    // Disable auto-incrementing since we're using UUIDs
    public $incrementing = false;

    // Set the key type to 'string' to support UUIDs
    protected $keyType = 'string';

    /**
     * Define an inverse one-to-many relationship with MediaInformation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mediaInformation()
    {
        return $this->belongsTo(MediaInformation::class, 'media_information_id', 'id');
    }

    public function getFullUrlAttribute()
{
    // Fetch storage URL and container dynamically from config
    $storageUrl = config('filesystems.disks.azure.url');
    $container = config('filesystems.disks.azure.container');
    
    // Use the media information to build the full URL
    return rtrim($storageUrl, '/') .  $this->mediaInformation->storage_path . '/' . $this->entity_id . '/' . $this->file_name;
}
}
