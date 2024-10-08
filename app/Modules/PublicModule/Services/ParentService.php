<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Country;
use App\Models\ModerationRequest;
use App\Models\Player;
use App\Models\PlayerParent;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;
use App\Traits\AzureBlobStorage;
use App\Traits\GeneralHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentService
{
    use GeneralHelpers;
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
                        'country_id' => $data['country'],
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
                    'country_id' => $data['country'],
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

    public function addNewChild (array $data, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        if($user) {
            $player_parent = PlayerParent::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->first();
            if($player_parent){
                $random_password = Str::random(8);
                $player_user = User::connect(config('database.default'))
                    ->create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'display_name' => $data['first_name'].' '.$data['last_name'],
                        'email' => $data['email'],
                        'slug' => $this->generateSlug(new User(), $data['first_name'].' '.$data['last_name'],'slug'),
                        'user_role_id' => config('app.user_roles.player'),
                        'user_type_id' => config('app.user_types.free'),
                        'country_id' => $data['country'],
                        'nationality_id' => $data['nationality'],
                        'gender' => $data['gender'],
                        'password' => Hash::make($random_password),
                        'remember_token' => Str::random(10)
                    ]);
                $player_user_phone = UserPhone::connect(config('database.secondary'))
                    ->where('user_id', $player_user->id)->first();
                $player_phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
                if(!$player_user_phone){
                    UserPhone::connect(config('database.default'))
                        ->create([
                            'user_id' => $player_user->id,
                            'country_id' => $data['phone_code_country'],
                            'is_default' => true,
                            'phone_code' => $player_phone_code,
                            'phone_number' => $data['phone_number'],
                        ]);
                }


                $player = Player::connect(config('database.secondary'))
                    ->where('user_id', $player_user->id)->first();
                if(!$player){
                    $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
                    $other_data = [
                        'utr' => $data['utr'],
                        'handedness' => $data['handedness'],
                    ];
                    $graduation_month_year = Carbon::createFromFormat('Y-m', $data['graduation_month_year']);

                    $sport = Sport::connect(config('database.secondary'))->first();

                    Player::connect(config('database.default'))
                        ->create([
                            'user_id' => $player_user->id,
                            'sport_id' => $sport->id,
                            'player_budget_id' => $data['budget'],
                            'player_parent_id' => $player_parent->id,
                            'has_parent' => true,
                            'graduation_month_year' => $graduation_month_year,
                            'gpa' => $data['gpa'],
                            'height' => $height,
                            'other_data' => $other_data
                        ]);
                }

                //Update child count on parent
                $player_parent->update([
                        'child_count' => $player_parent->child_count + 1
                    ]);

                // Create moderation request
                ModerationRequest::create([
                    'moderatable_type' => User::class,
                    'moderatable_id' => $player_user->id,
                    'priority' => 'medium',
                    'created_by' => $player_user->id,
                    'notes' => 'User signup requires approval*',
                ]);

                $player_user->sendEmailVerificationNotification();
            }
        }
    }

    public function updateChild (array $data, $user_id){
        $player_user = User::connect(config('database.default'))
            ->where('id', $user_id)
            ->first();
        if($player_user){
            $player_user->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'display_name' => $data['first_name'].' '.$data['last_name'],
                    'country_id' => $data['country'],
                    'nationality_id' => $data['nationality'],
                    'gender' => $data['gender']
                ]);

            $player = Player::connect(config('database.secondary'))
                ->where('user_id', $player_user->id)->first();
            if($player){
                $this ->getOtherData($user_id);

                $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
                $graduation_month_year = Carbon::createFromFormat('Y-m', $data['graduation_month_year']);

                $player->update([
                        'graduation_month_year' => $graduation_month_year,
                        'gpa' => $data['gpa'],
                        'height' => $height,
                        'other_data->utr' => $data['utr'],
                        'other_data->handedness' => $data['handedness'],
                        'other_data->budget_max' => $data['budget_max'],
                        'other_data->budget_min' => $data['budget_min'],
                    ]);
            }

            $player_user_phone = UserPhone::connect(config('database.secondary'))
                ->where('user_id', $player_user->id)->first();
            $player_phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
            if(!$player_user_phone){
                UserPhone::connect(config('database.default'))
                    ->create([
                        'user_id' => $player_user->id,
                        'country_id' => $data['phone_code_country'],
                        'is_default' => true,
                        'phone_code' => $player_phone_code,
                        'phone_number' => $data['phone_number'],
                    ]);
            }

            $player_user_email = User::connect(config('database.secondary'))
                ->where('email', $data['email'])
                ->where('id','!=', $player_user->id)
                ->first();
            if(!$player_user_email){
                if($player_user->email != $data['email']){
                    $player_user->update([
                        'email' => $data['email'],
                        'email_verified_at' => null,
                    ]);

                    //Send mail verification
                    $player_user->sendEmailVerificationNotification();
                }
            }
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
