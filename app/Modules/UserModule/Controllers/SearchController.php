<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserModule\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    private $searchService;

    function __construct()
    {
        //Init models
        $this->searchService = new SearchService();
    }

    public function search(Request $request)
    {  
        try{
            $dataSets = $this->searchService->search($request->all());

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

    public function getRecentSearch()
    {  
        try{
            $dataSets = $this->searchService->getRecentSearch();

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

    public function saveSearch(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'search_data' => 'required'
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }
            $this->searchService->saveSearch($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Search Created',
                'Successfully Search Created',
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function getSaveSearch()
    {  
        try{
            $dataSets = $this->searchService->getSaveSearch();

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

    public function deleteSaveSearch($search_id)
    {  
        try{
             $this->searchService->deleteSaveSearch($search_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Deleted',
                'Successfully Deleted',
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
