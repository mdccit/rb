<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Country;
use App\Models\Player;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;
use Carbon\Carbon;

class PlayerService
{
    public function updateBasicInfo (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $user->update([
                'first_name' => $data['first_name'],
                'other_names' => $data['other_names'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
            ]);
        }
    }

    public function updateBio (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $user->update([
                'bio' => $data['bio'],
            ]);
        }
    }

    public function updateContactInfo (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $user->update([
                'country_id' => $data['country'],
            ]);

            $user_email = User::connect(config('database.default'))
                ->where('email', $data['email'])
                ->where('id','!=', $user->id)
                ->first();
            if(!$user_email){
                if($user->email != $data['email']){
                    $user->update([
                        'email' => $data['email'],
                        'email_verified_at' => null,
                    ]);

                    //Send mail verification
                    $user->sendEmailVerificationNotification();
                }
            }

            //User phone
            $user_phone = UserPhone::connect(config('database.default'))
                ->where('user_id', $user->id)->first();
            $phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
            if(!$user_phone){
                UserPhone::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                        'country_id' => $data['phone_code_country'],
                        'is_default' => true,
                        'phone_code' => $phone_code,
                        'phone_number' => $data['phone_number'],
                    ]);
            }else{
                $user_phone->update([
                    'country_id' => $data['phone_code_country'],
                    'phone_code' => $phone_code,
                    'phone_number' => $data['phone_number'],
                ]);
            }

            //User address
            $user_address = UserAddress::connect(config('database.default'))
                ->where('user_id', $user->id)->first();
            if(!$user_address){
                UserAddress::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                        'country_id' => $data['phone_code_country'],
                        'is_default' => true,
                        'address_line_1' => $data['address_line_1'],
                        'address_line_2' => $data['address_line_2'],
                        'city' => $data['city'],
                        'state_province' => $data['state_province'],
                        'postal_code' => $data['postal_code'],
                        'type' => 'permanent',
                    ]);
            }else{
                $user_address->update([
                    'country_id' => $data['phone_code_country'],
                    'address_line_1' => $data['address_line_1'],
                    'address_line_2' => $data['address_line_2'],
                    'city' => $data['city'],
                    'state_province' => $data['state_province'],
                    'postal_code' => $data['postal_code'],
                ]);
            }

        }
    }

    public function updatePersonalOtherInfo (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $user->update([
                'nationality_id' => $data['nationality'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
            ]);

            $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
            $weight = $data['weight_in_kg']?$data['weight_kg']:$data['weight_lb']*0.4535;
            $graduation_month_year = Carbon::createFromFormat('Y-m', $data['graduation_month_year']);

            $this ->getOtherData($user->id);

            Player::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->update([
                    'graduation_month_year' => $graduation_month_year,
                    'height' => $height,
                    'weight' => $weight,
                    'other_data->handedness' => $data['handedness'],
                    'other_data->preferred_surface' => $data['preferred_surface'],
                ]);
        }
    }

    public function updateBudget (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $this ->getOtherData($user->id);
            Player::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->update([
                    'other_data->budget_max' => $data['budget_max'],
                    'other_data->budget_min' => $data['budget_min'],
                ]);
        }
    }

    public function updateCoreValues (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $this ->getOtherData($user->id);

            Player::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->update([
                    'gpa' => $data['gpa'],
                    'other_data->utr' => $data['utr'],
                    'other_data->sat_score' => $data['sat_score'],
                    'other_data->act_score' => $data['act_score'],
                    'other_data->toefl_score' => $data['toefl_score'],
                    'other_data->atp_ranking' => $data['atp_ranking'],
                    'other_data->itf_ranking' => $data['itf_ranking'],
                    'other_data->national_ranking' => $data['national_ranking'],
                    'other_data->wtn_score_manual' => $data['wtn_score_manual'],
                ]);
        }
    }

    private function getOtherData($user_id){
        $player = Player::connect(config('database.default'))
            ->where('user_id', $user_id)
            ->whereNull('other_data')
            ->first();
        if($player){
            $other_data = [
                'handedness' => '',
                'preferred_surface' => '',
                'budget_max' => 0,
                'budget_min' => 0,
                'utr' => 0,
                'sat_score' => 0,
                'act_score' => 0,
                'toefl_score' => 0,
                'atp_ranking' => 0,
                'itf_ranking' => 0,
                'national_ranking' => 0,
                'wtn_score_manual' => 0,
            ];

            $player->update([
                    'other_data' => $other_data,
                ]);
        }
    }
}
