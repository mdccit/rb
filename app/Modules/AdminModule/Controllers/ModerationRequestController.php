<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\ModerationRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ModerationRequest;

class ModerationRequestController extends Controller
{
    private $moderationRequestService;

    function __construct()
    {
        //Init models
        $this->moderationRequestService = new ModerationRequestService();
    }

    public function getAll(Request $request)
    {
        try{
            $dataSets = $this->moderationRequestService->getAll($request->all());

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

    public function get($morderation_id)
    {
        try{
            $morderation =ModerationRequest::connect(config('database.secondary'))->where('id',$morderation_id)->first();

            if($morderation){

                $dataSets = $this->moderationRequestService->get($morderation_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully morderation fetched',
                    'Successfully morderation fetched',
                    $dataSets
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'morderation does not exit',
                    'morderation does not exit'
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

    public function close($morderation_id , Request $request)
    {
        try{
            $morderation =ModerationRequest::connect(config('database.secondary'))->where('id',$morderation_id)->first();

            if($morderation){

                $this->moderationRequestService->close($morderation_id,$request->all());
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully morderation closed',
                    'Successfully morderation closed',
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'morderation does not exit',
                    'morderation does not exit'
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

    public function reopen($morderation_id , Request $request)
    {
        try{
            $morderation =ModerationRequest::connect(config('database.secondary'))->where('id',$morderation_id)->first();

            if($morderation){

                $this->moderationRequestService->reopen($morderation_id, $request->all());
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully morderation Reopened',
                    'Successfully morderation Reopened',
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'morderation does not exit',
                    'morderation does not exit'
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

    public function delete($morderation_id){
        try{
            $morderation =ModerationRequest::connect(config('database.secondary'))->where('id',$morderation_id)->first();

            if($morderation){

                $this->moderationRequestService->delete($morderation_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully morderation Deleted',
                    'Successfully morderation Deleted',
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'morderation does not exit',
                    'morderation does not exit'
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
