<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Country;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;
use App\Traits\AzureBlobStorage;

class ParentService
{
    use AzureBlobStorage;

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

        }
    }

    public function uploadProfilePicture ($file, $user_slug){
        $user = User::connect(config('database.secondary'))
            ->where('slug', $user_slug)
            ->first();
        $data = null;
        if($user) {
            $data = $this->uploadSingleFile($file, $user->id, 'user_profile_picture');
        }
        return $data;
    }

    public function uploadCoverPicture ($file, $user_slug){
        $user = User::connect(config('database.secondary'))
            ->where('slug', $user_slug)
            ->first();
        $data = null;
        if($user) {
            $data = $this->uploadSingleFile($file, $user->id, 'user_profile_cover');
        }
        return $data;
    }

    public function uploadMedia ($files, $user_slug){
        $user = User::connect(config('database.secondary'))
            ->where('slug', $user_slug)
            ->first();
        $dataArray = array();
        if($user) {
            $dataArray = $this->uploadMultipleFiles($files, $user->id, 'user_profile_media');
        }
        return $dataArray;
    }

    public function removeMedia ($media_id){
        return $this->removeFile($media_id);
    }
}
