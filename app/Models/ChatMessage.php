<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
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

    protected $fillable = ['type', 'message_status', 'content','created_by','conversation_id'];


    public function conversations()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id','id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function conversation()
    // {
    //     return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    // }
}
