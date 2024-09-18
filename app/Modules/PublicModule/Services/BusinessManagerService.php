<?php


namespace App\Modules\PublicModule\Services;


use App\Models\BusinessManager;
use App\Models\Country;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;

class BusinessManagerService
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

    public function updatePersonalOtherInfo (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $user->update([
                'country_id' => $data['country'],
                'nationality_id' => $data['nationality'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
            ]);

            BusinessManager::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->update([
                    'position' => $data['position'],
                ]);

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
}
