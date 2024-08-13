<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection = null)
    {
        $connection = $connection ?: config('database.default');
        return (new static)->setConnection($connection);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
