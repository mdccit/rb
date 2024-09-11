<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
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
        'user_id',
        'player_parent_id',
        'player_budget_id',
        'has_parent',
        'graduation_month_year',
        'gpa',
        'height',
        'weight',
//        'other_data',

        'other_data->handedness',
        'other_data->preferred_surface',

        'other_data->budget_max',
        'other_data->budget_min',

        'other_data->utr',
        'other_data->sat_score',
        'other_data->act_score',
        'other_data->toefl_score',
        'other_data->atp_ranking',
        'other_data->itf_ranking',
        'other_data->national_ranking',
        'other_data->wtn_score_manual',
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
