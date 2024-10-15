<?php


namespace App\Modules\UserModule\Services;

use App\Models\ResourceCategory;
use App\Models\Resource;

class ResourceService
{
    public function getAllResource (array $data){

        $query = Resource::connect(config('database.secondary'))
                    ->select(
                        'id',
                        'title',
                        'weight',
                        'content',
                        'category_id',
                        'created_at'
                );
        
        $dataSet = array();
       
        $dataSet = $query->get();
        

        return $dataSet;
    }

      public function getAllCategories (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = ResourceCategory::connect(config('database.secondary'))
                    ->select(
                        'id',
                        'title',
                        'description',
                        'icon',
                );
        if ($search_key != null) {
            $query->where('title', 'LIKE', '%' . $search_key . '%');
        }
        
        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
             $dataSet = $query->get();
        }

        return $dataSet;
    }

}