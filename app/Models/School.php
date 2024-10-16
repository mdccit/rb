<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
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
        'gov_id',
        'gov_sync_settings',
        'url',
        'genders_recruiting',
        'conference_id',
        'division_id',
        'other_data',

        'other_data->teams_count',
        'other_data->total_members',
        'other_data->editors',
        'other_data->viewers',
        'other_data->academics',//array
        'other_data->average_utr',
        'other_data->tuition_in_of_state',
        'other_data->tuition_out_of_state',
        'other_data->cost_of_attendance',
        'other_data->graduation_rate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'other_data' => 'array',
    ];

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class);
    }

}
