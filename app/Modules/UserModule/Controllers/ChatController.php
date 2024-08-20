<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\UserModule\Services\ChatService;
use App\Models\Resource;

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
               'message' => 'required|string|max:5000',
               'to_user_id' => 'required'
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

    public function deleteMessage(Request $request, $message_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'delete_type' => 'required|in:is_delete_from_user_chat,is_delete_to_user_chat',
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->chatService->deleteMessage($request->all(), $message_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Message Deleted',
                'Successfully Message Deleted'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }


    
}