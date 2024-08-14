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

        $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;

        $transferPlayer = TransferPlayer::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
                'school' => $data['school'],
                'utr_score_manual' => $data['utr_score_manual'],
                'year' => $data['year'],
                'win' => $data['win'],
                'loss' => $data['loss'],
                'profile_photo_path' => $data['profile_photo_path'],
                'handedness' => $data['handedness'],
                'email' => $data['email'],
                'phone_code' => $data['phone_code_country'],
                'phone_number' => $data['phone_number'],
                'height' => $height,
                'gender' => $data['gender'],
                'created_by' => auth()->id(),
            ]);
        

    }

    public function update(array $data, $transfer_id){

        $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;

        TransferPlayer::connect(config('database.default'))
        ->where('id', $transfer_id)
        ->update([
                'name' => $data['name'],
                'school' => $data['school'],
                'utr_score_manual' => $data['utr_score_manual'],
                'year' => $data['year'],
                'win' => $data['win'],
                'loss' => $data['loss'],
                'profile_photo_path' => $data['profile_photo_path'],
                'handedness' => $data['handedness'],
                'email' => $data['email'],
                'phone_code' => $data['phone_code_country'],
                'phone_number' => $data['phone_number'],
                'height' =>  $height,
                'gender' => $data['gender'],
        ]);
    }

    public function destroy(int $transfer_id){
       
        TransferPlayer::connect(config('database.default'))->destroy($transfer_id);
        
    }

}
