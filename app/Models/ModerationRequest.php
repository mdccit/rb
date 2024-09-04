<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ModerationRequest extends Model
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
    
    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'priority', // 'low', 'medium', 'high'
        'created_by',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    public function moderatable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->hasMany(ModerationComment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

   function getNumericPriorityAttribute()
    {
        $map = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
        ];

        return $map[$this->priority] ?? 0;
    }
}
