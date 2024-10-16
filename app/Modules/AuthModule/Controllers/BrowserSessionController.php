<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\BrowserSessionService;
use Illuminate\Http\Request;

class BrowserSessionController extends Controller
{
    private $browserSessionService;

    function __construct()
    {
        //Init models
        $this->browserSessionService = new BrowserSessionService();
    }

    public function logOutOtherBrowserSession(Request $request)
    {
        try{
           
            $this->browserSessionService->logOutOtherBrowserSession($request);
               
            return CommonResponse::getResponse(
                    200,
                   'Successfully Other Browser Logout',
                   'Successfully Other Browser Logout',         );
           
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    
}
