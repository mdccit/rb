<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\UserModule\Services\ResourceService;
use App\Models\Resource;

class ResourceController extends Controller
{
    private $resourceService;

    function __construct()
    {
        //Init models
        $this->resourceService = new ResourceService();
    }

    public function index(Request $request)
    {
        try{
            $dataSets = $this->resourceService->getAllResource($request->all());

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