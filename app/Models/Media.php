<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Media extends Model
{
    use HasFactory, HasUuids;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'media';

    // Specify which attributes can be mass-assigned
    protected $fillable = [
        'media_information_id',
        'entity_id',
        'entity_type',
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
}
