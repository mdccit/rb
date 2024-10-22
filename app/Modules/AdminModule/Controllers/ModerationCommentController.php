<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\ModerationCommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ModerationRequest;

class ModerationCommentController extends Controller
{
    private $moderationCommentService;

    function __construct()
    {
        //Init models
        $this->moderationCommentService = new ModerationCommentService();
    }

    public function getAll($mordaration_id)
    {
        try{
            $dataSets = $this->moderationCommentService->getAll($mordaration_id);

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

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|min:3|max:255',
                'morderation_id' => 'required|exists:moderation_requests,id'
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }
            $dataSets = $this->moderationCommentService->store($request->all());
                
            return CommonResponse::getResponse(
                200,
                'Successfully Mordaration Commented',
                'Successfully Mordaration Commented',
                $dataSets
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
