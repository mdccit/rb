<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationComment extends Model
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
        'moderation_request_id',
        'user_id',
        'comment',
    ];

    public function moderationRequest()
    {
        return $this->belongsTo(ModerationRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAuthor(User $user)
    {
        return $this->user_id === $user->id;
    }
}
