<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


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
     * @var array
     */
    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
    ];

    /**
     * Scope a query to only include jobs from a specific queue.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $queue
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInQueue($query, $queue)
    {
        return $query->where('queue', $queue);
    }

    /**
     * Scope a query to only include jobs that are available.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_at', '<=', now()->timestamp);
    }
}
