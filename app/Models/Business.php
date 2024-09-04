<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'bio',
        'slug',
        'is_verified',
        'is_approved',
        'url',
        'other_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'other_data' => 'array',
    ];
}
