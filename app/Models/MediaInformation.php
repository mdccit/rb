<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MediaInformation extends Model
{
    use HasFactory, HasUuids;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'media_information';

    // Specify which attributes can be mass-assigned
    protected $fillable = [
        'blob_name',
        'storage_path'
    ];

    // Disable auto-incrementing since we're using UUIDs
    public $incrementing = false;

    // Set the key type to 'string' to support UUIDs
    protected $keyType = 'string';

    /**
     * Define a one-to-many relationship with Media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(Media::class, 'media_information_id', 'id');
    }
}
