<?php


namespace App\Modules\UserModule\Services;

use App\Models\Conversation;
use App\Models\ChatMessage;
use  App\Services\AzureBlobStorageService;
use App\Traits\AzureBlobStorage;
class ConversationService
{
    use AzureBlobStorage;

    public function createConversation (array $data){
        $conversations = Conversation::connect(config('database.secondary'))
                        ->where(function ($query) use ($data) {
                            $query->where('conversations.user1_id', auth()->id())
                            ->where('conversations.user2_id', $data['user2_id']);
                        })
                       ->orWhere(function ($query) use ($data) {
                            $query->where('conversations.user1_id',$data['user2_id'])
                             ->Where('conversations.user2_id', auth()->id());
                        })
                        ->first();
        if(!$conversations){
            Conversation::connect(config('database.default'))
                ->create([
                    'user1_id' => auth()->id(),
                    'user2_id' => $data['user2_id'],
                    'is_delete_user1' => false,
                    'is_delete_user2' => false
                ]);
        }
       
    }
    
    public function deleteConversation (array $data, $conversation_id){
        Conversation::connect(config('database.default'))
            ->where('id', $conversation_id)
            ->update([
                $data['delete_type'] => true,
        ]);
    }

    public function getAllConversation(array $data){

        //$per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;

        $query = Conversation::connect(config('database.secondary'))
               
                ->with('messages')
                ->with('firstMessageUser')
                ->with('receivedUser')
                ->select(
                    'conversations.id',
                    'conversations.user1_id',
                    'conversations.user2_id',
                    'conversations.is_delete_user1',
                    'conversations.is_delete_user2',
                )->where(function ($query) {
                    $query->where('conversations.user1_id', auth()->id())
                          ->orWhere('conversations.user2_id', auth()->id());
                });

      
          //  $dataSet = $query->get();

       $dataSet = $query->get()->map(function ($conversation) {
               
                $user_id_1_profile_picture = $this->getSingleFileByEntityId($conversation->user1_id,'user_profile_picture');
                $conversation->user_1_profile_picture = $user_id_1_profile_picture;

                $user_id_2_profile_picture = $this->getSingleFileByEntityId($conversation->user2_id,'user_profile_picture');
                $conversation->user_2_profile_picture = $user_id_2_profile_picture;
    
                return $conversation;
            });
        
        return $dataSet;

    }
}