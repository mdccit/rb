<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\AdminModule\Services\ResourceService;
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

    public function store(Request $request){
        try{
            $validator = $this->validationResource($request);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->resourceService->create($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Resource Created',
                'Successfully Resource Created'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }

    }

    public function update(Request $request,$resource_id){
        try{
            
            $validator = $this->validationResource($request);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }
            $resources = Resource::connect(config('database.secondary'))->where('id', $resource_id)->first();
            if($resources){

                $this->resourceService->update($request->all(), $resource_id);
                
                return CommonResponse::getResponse(
                        200,
                        'Successfully Resource Updated',
                        'Successfully Resource Updated'
                    );
            }else{

                return CommonResponse::getResponse(
                    422,
                    'Resource does not exist',
                    'Resource does not exist'
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

    public function destroy($resource_id){
        try{
           
            $resources = Resource::connect(config('database.secondary'))->where('id', $resource_id)->first();

            if($resources){

                $this->resourceService->destroy($resource_id);

                return CommonResponse::getResponse(
                        200,
                        'Successfully Resource Deleted',
                        'Successfully Resource Deleted'
                    );
            }else{

                return CommonResponse::getResponse(
                    422,
                    'Resource does not exist',
                    'Resource does not exist'
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

    public  function validationResource($request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:3|max:9999999', 
            'weight' => 'required|integer',
            'category_id' => 'required|integer|exists:resource_categories,id',
        ]);

        return $validator;
        
    }

}