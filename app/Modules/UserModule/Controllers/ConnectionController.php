<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserModule\Services\ConnectionService;
use Illuminate\Http\Request;
use App\Models\ConnectionRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ConnectionController extends Controller
{
    private $connectionService;

    function __construct()
    {
        //Init models
        $this->connectionService = new ConnectionService();
    }

    public function requestConnection(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
               'receiver_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $existingRequest = ConnectionRequest::connect(config('database.secondary'))
                                ->whereIn('connection_status',['pending','accepted'])
                                ->where(function ($query) use ($request) {
                                    $query->where(function ($q) use ($request) {
                                        $q->where('sender_id', auth()->id())
                                          ->where('receiver_id', $request['receiver_id']);
                                    })
                                    ->orWhere(function ($q) use ($request) {
                                        $q->where('sender_id', $request['receiver_id'])
                                          ->where('receiver_id', auth()->id());
                                    });
                                })
                                ->exists();
            if($existingRequest){

                return CommonResponse::getResponse(
                    422,
                    'This user has already connection',
                    'This user has already connection'
                );

            }else{
                $responseData = $this->connectionService->requestConnection($request->all());

                return CommonResponse::getResponse(
                    200,
                    'Successfully Connection Added',
                    'Successfully Connection Added',
                    $responseData
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



    public function connectionAccept(Request $request,$connection_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'connection_status' => 'required|string|in:accepted'
            ]);
 
            if ($validator->fails())
            {
                 return CommonResponse::getResponse(
                     422,
                     $validator->errors()->all(),
                     'Input validation failed'
                 );
            }

            $existing = ConnectionRequest::connect(config('database.secondary'))
                            ->whereIn('connection_status',['pending'])
                            ->where('id',$connection_id)
                            ->exists();

            if($existing){

                $responseData = $this->connectionService->connectionStatusUpdate($request->all(),$connection_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Connection Accepted',
                    'Successfully Connection Accepted',
                    $responseData
                );
                                
                
            }else{

                return CommonResponse::getResponse(
                    422,
                    'This is not existing',
                    'This is not existing'
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

    public function connectionCancell(Request $request,$connection_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'connection_status' => 'required|string|in:cancelled'
            ]);
 
            if ($validator->fails())
            {
                 return CommonResponse::getResponse(
                     422,
                     $validator->errors()->all(),
                     'Input validation failed'
                 );
            }

            $existing = ConnectionRequest::connect(config('database.secondary'))
                            ->whereIn('connection_status',['pending'])
                            ->where('id',$connection_id)
                            ->exists();

            if($existing){

                $responseData = $this->connectionService->connectionStatusUpdate($request->all(),$connection_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Connection Accepted',
                    'Successfully Connection Accepted',
                    $responseData
                );
                                
                
            }else{

                return CommonResponse::getResponse(
                    422,
                    'This is not existing',
                    'This is not existing'
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

    public function connectionReject(Request $request,$connection_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'connection_status' => 'required|string|in:rejected'
            ]);
 
            if ($validator->fails())
            {
                 return CommonResponse::getResponse(
                     422,
                     $validator->errors()->all(),
                     'Input validation failed'
                 );
            }

            $existing = ConnectionRequest::connect(config('database.secondary'))
                            ->whereIn('connection_status',['pending'])
                            ->where('id',$connection_id)
                            ->exists();

            if($existing){

                $responseData = $this->connectionService->connectionStatusUpdate($request->all(),$connection_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Connection Accepted',
                    'Successfully Connection Accepted',
                    $responseData
                );
                                
                
            }else{

                return CommonResponse::getResponse(
                    422,
                    'This is not existing',
                    'This is not existing'
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


    public function connectionRemove(Request $request,$connection_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'connection_status' => 'required|string|in:removed'
            ]);
 
            if ($validator->fails())
            {
                 return CommonResponse::getResponse(
                     422,
                     $validator->errors()->all(),
                     'Input validation failed'
                 );
            }

            $existing = ConnectionRequest::connect(config('database.secondary'))
                            ->whereIn('connection_status',['accepted'])
                            ->where('id',$connection_id)
                            ->exists();

            if($existing){
                
                $this->connectionService->conversationRemove($connection_id);
                
                $responseData = $this->connectionService->connectionStatusUpdate($request->all(),$connection_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Connection Accepted',
                    'Successfully Connection Accepted',
                    $responseData
                );
                                
                
            }else{

                return CommonResponse::getResponse(
                    422,
                    'This is not existing',
                    'This is not existing'
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

    public function userinivitationAndConnectedList()
    {
        try{
            $dataSets = $this->connectionService->userinivitationAndConnectedList();

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

    public function connectionList($user_id)
    {
        try{
            $existing = User::connect(config('database.secondary'))
                            ->where('id',$user_id)
                            ->exists();

            if($existing){

                $responseData = $this->connectionService->userConnectionList($user_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Fetch Data',
                    'Successfully  Fetch Data',
                    $responseData
                );
                                
                
            }else{

                return CommonResponse::getResponse(
                    422,
                    'This user is not existing',
                    'This user is not existing'
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

    public function checkConnectionType($user_slug)
    {
        try{

            $user = User::connect(config('database.secondary'))
                            ->where('slug',$user_slug)
                            ->first();



            $responseData = $this->connectionService->checkConnectionType($user->id);
                
            return CommonResponse::getResponse(
                200,
                'Successfully Fetch Data',
                'Successfully  Fetch Data',
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


    public function invitationSendList()
    {
        try{
            $dataSets = $this->connectionService->invitationSendList();

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

    
}
