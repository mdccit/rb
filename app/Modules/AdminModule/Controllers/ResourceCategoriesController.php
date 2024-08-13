<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\AdminModule\Services\ResourceCategoriesService;
use App\Models\ResourceCategory;

class ResourceCategoriesController extends Controller
{
    private $resourceCategoriesService;

    function __construct()
    {
        //Init models
        $this->resourceCategoriesService = new ResourceCategoriesService();
    }

    public function index(Request $request)
    {
        try{
            $dataSets = $this->resourceCategoriesService->getAllCategories($request->all());

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

    public function storeCategory(Request $request){
        try{
            
            $validator = $this->validationResourceCategory($request);


            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->resourceCategoriesService->createCategory($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Category Created',
                'Successfully Category Created'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }

    }

    public function updateCategory(Request $request,$id){
        try{
            
            $validator = $this->validationResourceCategory($request);


            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }
            $resources_category = ResourceCategory::connect(config('database.secondary'))->where('id', $id)->first();
            if($resources_category){

                $this->resourceCategoriesService->updateCategory($request->all(), $id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully Category Updated',
                    'Successfully Category Updated'
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'Category does not exist',
                    'Category does not exist'
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

    public function destroyCategory($id){
        try{
            $resources_category = ResourceCategory::connect(config('database.secondary'))->where('id', $id)->first();
            if($resources_category){

                $this->resourceCategoriesService->destroyCategory($id);

                return CommonResponse::getResponse(
                        200,
                        'Successfully Category Deleted',
                        'Successfully Category Deleted'
                    );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'Category does not exist',
                    'Category does not exist'
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

    public  function validationResourceCategory($request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
			'description' => 'required|string',
			'icon' => 'required|string|max:255',
        ]);

        return $validator;
        
    }

}