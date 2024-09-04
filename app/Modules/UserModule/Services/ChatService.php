<?php


namespace App\Modules\UserModule\Services;

use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function sendMessage (array $data){
       ChatMessage::connect(config('database.default'))
        ->create([
            'content' => $data['content'],
            'type' => 'text',
            'message_status' => 'sent',
            'created_by' => auth()->id(),
            'conversation_id' => $data['conversation_id'],
        ]);
    }
    
   
    public function unreadMessageCount(){

        $count = Conversation::connect(config('database.secondary'))
                   ->leftJoin('chat_messages', function($join) {
                 $join->on('conversations.id', '=', 'chat_messages.conversation_id')
                    ->where('chat_messages.message_status', '=', 'sent')
                    ->where('chat_messages.created_by', '!=', auth()->id());
                })
                ->where(function($query) {
                    $query->where(function($query) {
                        $query->where('conversations.user1_id', '=', auth()->id())
                        ->where('conversations.is_delete_user1', '=', 0);
                })
                ->orWhere(function($query) {
                    $query->where('conversations.user2_id', '=', auth()->id())
                    ->where('conversations.is_delete_user2', '=', 0);
                });
            })
             ->count(DB::raw('DISTINCT chat_messages.id'));

        return $count;

    }

    public function messages($conversation_id)
    {
        return ChatMessage::where('conversation_id',$conversation_id)->get();
    }
}