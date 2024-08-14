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

}