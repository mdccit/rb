<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
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

    protected $fillable = ['user1_id', 'user2_id', 'is_delete_user1','is_delete_user2'];


    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function firstMessageUser()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function receivedUser()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
