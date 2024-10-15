<?php


namespace App\Modules\AdminModule\Services;

use App\Models\ResourceCategory;

class ResourceCategoriesService
{
    public function getAllCategories (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = ResourceCategory::connect(config('database.secondary'))
                    ->select(
                        'id',
                        'title',
                        'description',
                        'icon',
                        'created_at'
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

    public function createCategory(array $data){
        $resource_category = ResourceCategory::connect(config('database.default'))
                        ->create([
                           'title' => $data['title'],
                           'description' => $data['description'],
                           'icon' => $data['icon'],
                        ]);
    }

    public function updateCategory(array $data, int $category_id){
        ResourceCategory::connect(config('database.default'))
                ->where('id', $category_id)
                ->update([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                ]);
        
    }

    public function destroyCategory(int $category_id){
        
        ResourceCategory::connect(config('database.default'))->destroy($category_id);
        
    }
}