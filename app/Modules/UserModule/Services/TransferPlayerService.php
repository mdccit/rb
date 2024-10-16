<?php


namespace App\Modules\UserModule\Services;

use App\Models\TransferPlayer;
use App\Traits\AzureBlobStorage;
use Illuminate\Support\Facades\DB;

class TransferPlayerService
{
    use AzureBlobStorage;

    public function getAllUsers(array $data)
    {

        $per_page_items = array_key_exists("per_page_items", $data) ? $data['per_page_items'] : 0;
        $search_key = array_key_exists("search_key", $data) ? $data['search_key'] : null;
        $utr_min = array_key_exists("utr_min", $data) ? $data['utr_min'] : null;
        $utr_max = array_key_exists("utr_max", $data) ? $data['utr_max'] : null;


        $year = config('app.year');
        $junior = $year['junior'];
        $senior = $year['senior'];
        $freshman = $year['freshman'];
        $sophomore = $year['sophomore'];

        $query = TransferPlayer::connect(config('database.secondary'))
            ->join('sports', 'sports.id', '=', 'transfer_players.sport_id')
            ->select(
                'transfer_players.id as id',
                'first_name',
                'last_name',
                'school',
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
                'email',
                'phone_code',
                'phone_number',
                'height',
                'gender',
                'created_by',
                'other_data->utr_score_manual as utr_score_manual',
                'other_data->handedness as handedness',
                'sports.name as sport_name',
            );


        if ($search_key != null) {
            $query->where(function ($q) use ($search_key) {
                $q->where('first_name', 'LIKE', '%' . $search_key . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $search_key . '%');
            });
        }

        if ($utr_min != null) {
            $query->where('other_data->utr_score_manual', '>=', $utr_min);
        }

        if ($utr_max != null) {
            $query->where('other_data->utr_score_manual', '<=', $utr_max);
        }


        $dataSet = array();
        if ($per_page_items != 0) {
            $dataSet = $query->paginate($per_page_items);
        } else {
            $dataSet = $query->get();
        }

        foreach ($dataSet as $key => $value) {
            $dataSet[$key]['media'] = $this->getSingleFileByEntityId($value['id'], 'transfer_user_profile_picture');
        }

        return $dataSet;
    }

    public function getMedia ($transfer_id){
        return $this->getSingleFileByEntityId( $transfer_id, 'transfer_user_profile_picture');
    }
}
