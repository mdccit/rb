<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserModule\Services\ConnectionService;
use Illuminate\Http\Request;
use App\Models\ConnectionRequest;
use Illuminate\Support\Facades\Validator;

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
                                    $query->where('sender_id', auth()->id())
                                        ->where('receiver_id', $request['receiver_id']);
                                })
                                ->orWhere(function ($query) use ($request) {
                                    $query->where('sender_id', $request['receiver_id'])
                                        ->where('receiver_id', auth()->id());
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

    

    
}
