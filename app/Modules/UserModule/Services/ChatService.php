<?php


namespace App\Modules\UserModule\Services;

use App\Models\ChatMessage;
use Log;
class ChatService
{
    public function sendMessage (array $data){
       ChatMessage::connect(config('database.default'))
        ->create([
            'content' => $data['message'],
            'type' => 'text',
            'is_delete_from_user_chat'=> false,
            'is_delete_to_user_chat' => false,
            'seen' => false,
            'from_user_id' => auth()->id(),
            'to_user_id' => $data['to_user_id'],
        ]);
    }
    
    public function deleteMessage (array $data, $message_id){
        ChatMessage::connect(config('database.default'))
            ->where('id', $message_id)
            ->update([
                $data['delete_type'] => true,
        ]);
     }
}