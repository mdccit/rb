<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'type', 'title', 'description', 'seo_url'];

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($post) {

            if (empty($post->id)) {
                $post->id = (string) Str::uuid();
            }
            // If the post type is 'post', use the UUID as the seo_url
            if ($post->type === 'post') {
                $post->seo_url = Str::uuid();
            } else {
                // For other types, generate an SEO-friendly slug from the title
                $post->seo_url = Str::slug($post->title, '-');
            }
        });
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

    const TYPES = [
        'POST' => 'post',
        'EVENT' => 'event',
        'BLOG' => 'blog',
    ];
}
