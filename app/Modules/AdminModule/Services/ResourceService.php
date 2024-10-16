<?php


namespace App\Modules\AdminModule\Services;

use App\Models\ResourceCategory;
use App\Models\Resource;

class ResourceService
{
    public function getAllResource (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = Resource::connect(config('database.secondary'))
                    ->select(
                        'id',
                        'title',
                        'weight',
                        'content',
                        'category_id',
                        'created_at'
                )->orderBy('created_at', 'DESC');
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
    
    public function getResource ($id){
       

        $dataSet = Resource::connect(config('database.secondary'))
                    ->where('id','=',$id)
                    ->select(
                        'id',
                        'title',
                        'weight',
                        'content',
                        'category_id',
                        'created_at'
                )->first();
       
        return $dataSet;
    }
    public function create(array $data){
        $resource = Resource::connect(config('database.default'))
                    ->create([
                        'title' => $data['title'],
			            'content' => $data['content'],
			            'weight' => $data['weight'],
			            'category_id' => $data['category_id'],
			            'created_by' => auth()->id(),
                    ]);
    }

    public function update(array $data, int $resource_id){
        
        Resource::connect(config('database.default'))
                ->where('id', $resource_id)
                ->update([
                   'title' => $data['title'],
			       'content' => $data['content'],
			       'weight' => $data['weight'],
			       'category_id' => $data['category_id'],
                ]);
    
    }

    public function destroy(int $resource_id){
       
        Resource::connect(config('database.default'))->destroy($resource_id);
        
    }
}