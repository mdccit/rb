<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Player;
use App\Models\User;
use App\Models\UserAddress;

class PlayerService
{
    

    public function getUser ($user_id){

        $user = User::connect(config('database.secondary'))
                ->join('players', 'players.user_id', '=' ,'users.id')
                ->where('users.id', $user_id)
                ->select(
                    'users.id',
                    'users.gender',
                    'users.date_of_birth',
                    'players.graduation_month_year',
                    'players.gpa',
                    'players.height',
                    'players.weight',
                    'players.other_data'
                )
                ->first();

        $user_address =  UserAddress::connect(config('database.secondary'))
                        ->where('user_id', $user_id)
                        ->where('is_default','=', true)
                        ->select(
                            'is_default',
                            'address_line_1',
                            'address_line_2',
                            'city',
                            'state_province',
                            'postal_code',
                            'type',
                        )
                        ->first();
        return [
                'user_profile_info' => $user,
                'user_address_info' => $user_address,
            ];
        
    }

   
    public function updateUser(array $data, $user_id){
        $user = User::connect(config('database.default'))
            ->where('id', $user_id)
            ->first();

        $user->update([
            'gender' =>isset($data['gender']) ? $data['gender'] : null,
            'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : null,
        ]);

        $others_data = [
            'preferred_surface' =>isset($data['preferred_surface']) ? $data['preferred_surface'] : null,
            'handedness' =>isset($data['handedness']) ? $data['handedness'] : null,
            'budget_max' => isset($data['budget_max']) ? $data['budget_max'] : null,
            'budget_min' => isset($data['budget_min']) ? $data['budget_min'] : null,
            'utr' => isset($data['utr']) ? $data['utr'] : null,
            'sat_score' => isset($data['sat_score']) ? $data['sat_score'] : null,
            'act_score' => isset($data['act_score']) ? $data['act_score'] : null,
            'toefl_score' => isset($data['toefl_score']) ? $data['toefl_score'] : null,
            'atp_ranking' => isset($data['atp_ranking']) ? $data['atp_ranking'] : null,
            'itf_ranking' => isset($data['itf_ranking']) ? $data['itf_ranking'] : null,
            'national_ranking' => isset($data['national_ranking']) ? $data['national_ranking'] : null,
            'wtn_score_manual' => isset($data['wtn_score_manual']) ? $data['wtn_score_manual'] : null,
        ];
        //Player
        $player = Player::connect(config('database.default'))
                    ->where('user_id', $user->id)->first();
        $player->update([
            'graduation_month_year' =>isset($data['graduation_month_year']) ? $data['graduation_month_year'] : null,
            'gpa' => isset($data['gpa']) ? $data['gpa'] : null,
            'height' => isset($data['height_cm']) ? $data['height_cm'] : null,
            'weight' => isset($data['weight']) ? $data['weight'] : null,
            'other_data' => $others_data,
        ]);
        
        $addressData = [
            'country_id' => isset($data['country']) ? $data['country'] : null,
            'is_default' =>  true,
            'address_line_1' => isset($data['address_line_1']) ? $data['address_line_1'] : null,
            'address_line_2' => isset($data['address_line_2']) ? $data['address_line_2'] : null,
            'city' => isset($data['city']) ? $data['city'] : null,
            'state_province' => isset($data['state_province']) ? $data['state_province'] : null,
            'postal_code' => isset($data['postal_code']) ? $data['postal_code'] : null,
            'type' => 'permanent'
        ];
        //user address
        UserAddress::updateOrCreate(
            ['user_id' => $user_id], 
            $addressData 
        );




    }

}
