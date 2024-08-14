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
            // If the post type is 'post', use the UUID as the seo_url
            if ($post->type === 'post') {
                $post->seo_url = Str::uuid();
            } else {
                // For other types, generate an SEO-friendly slug from the title
                $post->seo_url = Str::slug($post->title, '-');
            }
        });
    }


    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }


    const TYPES = [
        'POST' => 'post',
        'EVENT' => 'event',
        'BLOG' => 'blog',
    ];
}
