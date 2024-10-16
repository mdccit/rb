<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferPlayer extends Model
{
    use HasFactory;

     /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'school',
        'utr_score_manual',
        'year',
        'win',
        'loss',
        'profile_photo_path',
        'handedness',
        'email',
        'country_id',
        'phone_code',
        'phone_number',
        'height',
        'gender',
        'other_data',
        'sport_id',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
