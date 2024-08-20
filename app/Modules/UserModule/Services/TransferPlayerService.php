<?php


namespace App\Modules\UserModule\Services;

use App\Models\TransferPlayer;
use DB;

class TransferPlayerService
{
    public function getAllUsers (array $data){

        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $utr_min = array_key_exists("utr_min",$data)?$data['utr_min']:null;
        $utr_max = array_key_exists("utr_max",$data)?$data['utr_max']:null;
        $gender = array_key_exists("gender",$data)?$data['gender']:null;

       
       $year = config('app.year');
       $junior = $year['junior'];
       $senior = $year['senior'];
       $freshman = $year['freshman'];
       $sophomore = $year['sophomore'];

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

       
        if ($utr_min != null) {
            $query->where('utr_score_manual', '>=', $utr_min);
        }

        if ($utr_max != null) {
            $query->where('utr_score_manual', '<=', $utr_max);
        }

        if ($gender != null) {
            $query->where('gender','=', $gender);
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
