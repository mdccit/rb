<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Post extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['user_id', 'type', 'title', 'description', 'seo_url','school_id','business_id','publisher_type'];

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection = null)
    {
        $connection = $connection ?: config('database.default');
        return (new static)->setConnection($connection);
    }

    public static function boot()
    {
        parent::boot();
    }

    public $incrementing = false; // Since we're using UUIDs, auto-increment should be disabled
    protected $keyType = 'string'; // Ensure that the primary key type is string

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function media()
{
    return $this->hasMany(Media::class, 'entity_id', 'id')
                ->where('entity_type', 'post');
}

    const TYPES = [
        'POST' => 'post',
        'EVENT' => 'event',
        'BLOG' => 'blog',
    ];
}
