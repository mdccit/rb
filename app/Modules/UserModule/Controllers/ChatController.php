<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\UserModule\Services\ChatService;
use App\Models\Conversation;

class ChatController extends Controller
{
    private $chatService;

    function __construct()
    {
        //Init models
        $this->chatService = new ChatService();
    }

    public function sendMessage(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
               'content' => 'required|string|max:5000',
               'conversation_id' => 'required|exists:conversations,id'
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->chatService->sendMessage($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Message send',
                'Successfully Message send'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    
    public function unreadMessageCount(){
        try{

            $dataSets = $this->chatService->unreadMessageCount();

            $responseData = [
                'count' => $dataSets,
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

    public function getMessages($conversation_id){
        try{
            $conversation = Conversation::connect(config('database.secondary'))->where('id', $conversation_id)->first();
            if($conversation){
                $dataSets = $this->chatService->messages($conversation_id);

                $responseData = [
                    'dataSets' => $dataSets,
                ];

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                   $responseData
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