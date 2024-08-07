<?php

namespace App\Modules\CommonModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\AuthService;
use App\Modules\CommonModule\Services\DefaultDataService;
use Illuminate\Http\Request;

class DefaultDataController extends Controller
{
    private $defaultDataService;

    function __construct()
    {
        //Init models
        $this->defaultDataService = new DefaultDataService();
    }

    public function loadComboList(Request $request)
    {
        try{
            $responseData = [
                'genders' => $this->defaultDataService->getGenders(),
                'handedness' => $this->defaultDataService->getHandedness(),
                'player_budgets' => $this->defaultDataService->getPlayerBudgets(),
                'countries' => $this->defaultDataService->getCountries(),
                'nationalities' => $this->defaultDataService->getNationalities(),
            ];

            return CommonResponse::getResponse(
                200,
                'Successfully Registered',
                'Successfully Registered',
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
