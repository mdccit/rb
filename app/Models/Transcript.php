<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection = null)
    {
        $connection = $connection ?: config('database.default');
        return (new static)->setConnection($connection);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'player_id',
        'country_id',
        'language',
        'file_name',
        'status',
        'processed_at',
        'local_gpa',
        'american_gpa'
    ];
}
