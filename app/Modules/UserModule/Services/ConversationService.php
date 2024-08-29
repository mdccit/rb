<?php


namespace App\Modules\UserModule\Services;

use App\Models\Conversation;

class ConversationService
{
    public function createConversation (array $data){
        Conversation::connect(config('database.default'))
        ->create([
           'user1_id' => auth()->id(),
           'user2_id' => $data['user2_id'],
           'is_delete_user1' => false,
           'is_delete_user2' => false
        ]);
    }
    
    public function deleteConversation (array $data, $conversation_id){
        Conversation::connect(config('database.default'))
            ->where('id', $conversation_id)
            ->update([
                $data['delete_type'] => true,
        ]);
    }

    public function getAllConversation(array $data){

        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;

        $query = Conversation::connect(config('database.secondary'))
                ->select(
                    'id',
                    'user1_id',
                    'user2_id',
                    'is_delete_user1',
                    'is_delete_user2'
                );

        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }
        
        return $dataSet;

    }
}