<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['feed_id', 'type', 'title', 'description'];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
