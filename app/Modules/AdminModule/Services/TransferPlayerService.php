<?php


namespace App\Modules\AdminModule\Services;

use App\Models\TransferPlayer;
use DB;

class TransferPlayerService
{
    public function getAllUsers (array $data){

        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;
        
       
       $year = config('app.year');
       $junior = addslashes($year['junior']);
       $senior = addslashes($year['senior']);
       $freshman = addslashes($year['freshman']);
       $sophomore = addslashes($year['sophomore']);

        $query = TransferPlayer::connect(config('database.secondary'))
                ->select(
                   'id',
                   'name',
                   'school',
                   'utr_score_manual',
                    DB::raw("
                        CASE 
                            WHEN year = 'junior' THEN '$junior'
                            WHEN year = 'senior' THEN '$senior'
                            WHEN year = 'freshman' THEN '$freshman'
                            WHEN year = 'sophomore' THEN '$sophomore'
                            ELSE year
                            END as year
                 "),
                   'win',
                   'loss',
                   'profile_photo_path',
                   'handedness',
                   'email',
                   'phone_code',
                   'phone_number',
                   'height',
                   'gender',
                   'created_by',
                );

       
        if ($search_key != null) {
            $query->where('name', 'LIKE', '%' . $search_key . '%');
        }


        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;

    }

    public function store(array $data){

        $height = null;

        if(isset($data['height_in_cm'])){

            $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
        }

        TransferPlayer::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
                'school' => $data['school'],
                'utr_score_manual' => $data['utr_score_manual'],
                'year' => isset($data['year']) ? $data['year'] : 'freshman',
                'win' =>  isset($data['win']) ? $data['win'] : 0 ,
                'loss' => isset($data['loss'])? $data['loss'] : 0,
                'profile_photo_path' => isset($data['profile_photo_path'])? $data['profile_photo_path'] : null,
                'handedness' => isset($data['handedness'])? $data['handedness'] : 'both',
                'email' => isset($data['email'])? $data['email'] :null,
                'phone_code' => isset($data['phone_code_country'])? $data['phone_code_country'] : null,
                'phone_number' => isset($data['phone_number'])? $data['phone_number'] : null,
                'height' => $height,
                'gender' => isset($data['gender'])? $data['gender'] : 'none',
                'created_by' => auth()->id(),
            ]);
        

    }

    public function update(array $data, $transfer_id){

        $height = null;

        if(isset($data['height_in_cm'])){

            $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
        }

        TransferPlayer::connect(config('database.default'))
        ->where('id', $transfer_id)
        ->update([
                'name' => $data['name'],
                'school' => $data['school'],
                'utr_score_manual' => $data['utr_score_manual'],
                'year' => isset($data['year']) ? $data['year'] : 'freshman',
                'win' =>  isset($data['win']) ? $data['win'] : 0 ,
                'loss' => isset($data['loss'])? $data['loss'] : 0,
                'profile_photo_path' => isset($data['profile_photo_path'])? $data['profile_photo_path'] : null,
                'handedness' => isset($data['handedness'])? $data['handedness'] : 'both',
                'email' => isset($data['email'])? $data['email'] :null,
                'phone_code' => isset($data['phone_code_country'])? $data['phone_code_country'] : null,
                'phone_number' => isset($data['phone_number'])? $data['phone_number'] : null,
                'height' => $height,
                'gender' => isset($data['gender'])? $data['gender'] : 'none',
        ]);
    }

    public function destroy(int $transfer_id){
       
        TransferPlayer::connect(config('database.default'))->destroy($transfer_id);
        
    }

}
