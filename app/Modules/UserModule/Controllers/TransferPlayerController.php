<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserModule\Services\TransferPlayerService;
use Illuminate\Http\Request;

class TransferPlayerController extends Controller
{
    private $transferPlayerService;

    function __construct()
    {
        //Init models
        $this->transferPlayerService = new TransferPlayerService();
    }

    public function getAllUsers(Request $request)
    {
        try{
            $dataSets = $this->transferPlayerService->getAllUsers($request->all());

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
