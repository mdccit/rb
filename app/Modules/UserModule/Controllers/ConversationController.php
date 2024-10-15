<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\UserModule\Services\ConversationService;
use App\Models\Conversation;

class ConversationController extends Controller
{
    private $conversationService;

    function __construct()
    {
        //Init models
        $this->conversationService = new ConversationService();
    }

    public function getAllConversation(Request $request)
    {
        try{
        
            
            $dataSets = $this->conversationService->getAllConversation($request->all());

            $responseData = [
                'dataSets' => $dataSets,
            ];

            return CommonResponse::getResponse(
                200,
                'Successfully fetched',
                'Successfully fetched',
                $responseData
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function createConversation(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
               'user2_id' => 'required'
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->conversationService->createConversation($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Conversation Created',
                'Successfully Conversation Created'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function deleteConversation(Request $request, $conversation_id)
    {
        try{

            $validator = Validator::make($request->all(), [
                'delete_type' => 'required'
             ]);
 
             if ($validator->fails())
             {
                 return CommonResponse::getResponse(
                     422,
                     $validator->errors(),
                     'Input validation failed'
                 );
             }
             $conversation = Conversation::connect(config('database.secondary'))->where('id', $conversation_id)->first();
             if($conversation){
 
                $this->conversationService->deleteConversation($request->all(), $conversation_id);

                 return CommonResponse::getResponse(
                    200,
                    'Successfully Conversation Deleted',
                    'Successfully Conversation Deleted'
                  );
             }else{
 
                 return CommonResponse::getResponse(
                     422,
                     'Conversation does not exist',
                     'Conversation does not exist'
                 ); 
             }
           
            
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

   
    
}
