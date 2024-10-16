<?php


namespace App\Modules\AdminModule\Services;

use App\Models\TransferPlayer;
use App\Models\Country;
use App\Models\Sport;
use App\Traits\AzureBlobStorage;
use Illuminate\Support\Facades\DB;

class TransferPlayerService
{
    use AzureBlobStorage;



    public function getUser(int $transfer_id)
    {
        return TransferPlayer::connect(config('database.default'))->where('id', $transfer_id)->first();
    }

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
                'sports.name as sport_name'
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

        return $dataSet;
    }

    public function store(array $data)
    {

        $height = null;

        if (isset($data['height_in_cm']) || isset($data['height_ft']) || isset($data['height_in'])) {

            $height = $data['height_in_cm'] ? $data['height_cm'] : (($data['height_ft'] * 12) + $data['height_in']) * 2.54;
        }

        $phone_code = null;

        if (isset($data['phone_code_country'])) {

            $phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
        }

        $otherData = [
            'utr_score_manual' => $data['utr_score_manual'],
            'handedness' => isset($data['handedness']) ? $data['handedness'] : 'both',
        ];

        $sport = Sport::connect(config('database.secondary'))->first();

        TransferPlayer::connect(config('database.default'))
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'school' => $data['school'],
                'year' => isset($data['year']) ? $data['year'] : 'freshman',
                'win' =>  isset($data['win']) ? $data['win'] : 0,
                'loss' => isset($data['loss']) ? $data['loss'] : 0,
                'profile_photo_path' => isset($data['profile_photo_path']) ? $data['profile_photo_path'] : null,
                'email' => isset($data['email']) ? $data['email'] : null,
                'country_id' => isset($data['phone_code_country']) ? $data['phone_code_country'] : null,
                'phone_code' =>  $phone_code,
                'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : null,
                'height' => $height,
                'gender' => isset($data['gender']) ? $data['gender'] : 'none',
                'created_by' => auth()->id(),
                'other_data' => json_encode($otherData),
                'sport_id' => $sport->id,
            ]);
    }

    public function update(array $data, $transfer_id)
    {

        $height = null;

        if (isset($data['height_in_cm']) || isset($data['height_ft']) || isset($data['height_in'])) {

            $height = $data['height_in_cm'] ? $data['height_cm'] : (($data['height_ft'] * 12) + $data['height_in']) * 2.54;
        }

        $phone_code = null;

        if (isset($data['phone_code_country'])) {

            $phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
        }

        $otherData = [
            'utr_score_manual' => $data['utr_score_manual'],
            'handedness' => isset($data['handedness']) ? $data['handedness'] : 'both',
        ];

        $sport = Sport::connect(config('database.secondary'))->first();


        TransferPlayer::connect(config('database.default'))
            ->where('id', $transfer_id)
            ->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'school' => $data['school'],
                'year' => isset($data['year']) ? $data['year'] : 'freshman',
                'win' =>  isset($data['win']) ? $data['win'] : 0,
                'loss' => isset($data['loss']) ? $data['loss'] : 0,
                'profile_photo_path' => isset($data['profile_photo_path']) ? $data['profile_photo_path'] : null,
                'email' => isset($data['email']) ? $data['email'] : null,
                'country_id' => isset($data['phone_code_country']) ? $data['phone_code_country'] : null,
                'phone_code' =>  $phone_code,
                'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : null,
                'height' => $height,
                'gender' => isset($data['gender']) ? $data['gender'] : 'none',
                'created_by' => auth()->id(),
                'other_data' => json_encode($otherData),
                'sport_id' => $sport->id,
            ]);
    }

    public function destroy(int $transfer_id)
    {

        TransferPlayer::connect(config('database.default'))->destroy($transfer_id);
    }

    public function uploadProfilePicture($file, $transferPlayerId)
    {
        return $this->uploadSingleFile($file, $transferPlayerId, 'transfer_user_profile_picture');
    }

    public function removeMedia ($media_id){
        return $this->removeFile($media_id);
    }

    public function getMedia ($transfer_id){
        return $this->getSingleFileByEntityId( $transfer_id, 'transfer_user_profile_picture');
    }
}
