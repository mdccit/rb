<?php


namespace App\Modules\AuthModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Country;
use App\Models\Player;
use App\Models\PlayerBudget;
use App\Models\PlayerParent;
use App\Models\User;
use App\Models\UserPhone;
use App\Traits\GeneralHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterService
{
    use GeneralHelpers;

    public function createPlayer(array $data, $user){
        User::connect(config('database.default'))
            ->where('id', $user->id)
            ->update([
                'country_id' => $data['country'],
                'nationality_id' => $data['nationality'],
                'user_role_id' => config('app.user_roles.player'),
                'gender' => $data['gender'],
        ]);
        $user_phone = UserPhone::connect(config('database.secondary'))
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
        }


        $player = Player::connect(config('database.secondary'))
            ->where('user_id', $user->id)->first();
        if(!$player){
            $player_budget = PlayerBudget::connect(config('database.secondary'))->find($data['player_budget']);
            $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
            $other_data = [
                'utr' => $data['utr'],
                'handedness' => $data['handedness'],
                'budget_min' => $player_budget->budget_min,
                'budget_max' => $player_budget->budget_max,
            ];
            $graduation_month_year = Carbon::createFromFormat('Y-m', $data['graduation_month_year']);

            Player::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                    'player_budget_id' => $data['player_budget'],
                    'graduation_month_year' => $graduation_month_year,
                    'gpa' => $data['gpa'],
                    'height' => $height,
                    'other_data' => $other_data
                ]);
        }
    }

    public function createCoach(array $data, $user){
        User::connect(config('database.default'))
            ->where('id', $user->id)
            ->update([
                'country_id' => $data['country'],
                'user_role_id' => config('app.user_roles.coach'),
            ]);

        $user_phone = UserPhone::connect(config('database.secondary'))
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
        }


        $coach = Coach::connect(config('database.secondary'))
            ->where('user_id', $user->id)->first();
        if(!$coach){
            Coach::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }

    public function createBusinessManager(array $data, $user){
        User::connect(config('database.default'))
            ->where('id', $user->id)
            ->update([
                'country_id' => $data['country'],
                'user_role_id' => config('app.user_roles.business_manager'),
            ]);

        $user_phone = UserPhone::connect(config('database.secondary'))
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
        }


        $business_manager = BusinessManager::connect(config('database.secondary'))
            ->where('user_id', $user->id)->first();
        if(!$business_manager){
            BusinessManager::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }

    public function createParent(array $data, $user){
        //Parent
        User::connect(config('database.default'))
            ->where('id', $user->id)
            ->update([
                'country_id' => $data['country'],
                'user_role_id' => config('app.user_roles.parent'),
            ]);

        $user_phone = UserPhone::connect(config('database.secondary'))
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
        }


        $player_parent = PlayerParent::connect(config('database.secondary'))
            ->where('user_id', $user->id)->first();
        if(!$player_parent){
            $player_parent = PlayerParent::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                    'child_count' => 1
                ]);
        }

        //Player
        $random_password = Str::random(8);
        $player_user = User::connect(config('database.default'))
            ->create([
                'first_name' => $data['player_first_name'],
                'last_name' => $data['player_last_name'],
                'display_name' => $data['player_first_name'].' '.$data['player_last_name'],
                'email' => $data['email'],
                'slug' => $this->generateSlug(new User(), $data['player_first_name'].' '.$data['player_last_name'],'slug'),
                'user_role_id' => config('app.user_roles.player'),
                'user_type_id' => config('app.user_types.free'),
                'country_id' => $data['player_country'],
                'nationality_id' => $data['player_nationality'],
                'gender' => $data['player_gender'],
                'password' => Hash::make($random_password),
                'remember_token' => Str::random(10)
            ]);
        $player_user_phone = UserPhone::connect(config('database.secondary'))
            ->where('user_id', $player_user->id)->first();
        $player_phone_code = Country::connect(config('database.secondary'))->find($data['player_phone_code_country'])->getPhoneCode();
        if(!$player_user_phone){
            UserPhone::connect(config('database.default'))
                ->create([
                    'user_id' => $player_user->id,
                    'country_id' => $data['player_phone_code_country'],
                    'is_default' => true,
                    'phone_code' => $player_phone_code,
                    'phone_number' => $data['player_phone_number'],
                ]);
        }


        $player = Player::connect(config('database.secondary'))
            ->where('user_id', $player_user->id)->first();
        if(!$player){
            $height = $data['player_height_in_cm']?$data['player_height_cm']:(($data['player_height_ft']*12)+$data['player_height_in'])*2.54;
            $other_data = [
                'utr' => $data['player_utr'],
                'handedness' => $data['player_handedness'],
            ];
            $graduation_month_year = Carbon::createFromFormat('Y-m', $data['player_graduation_month_year']);

            Player::connect(config('database.default'))
                ->create([
                    'user_id' => $player_user->id,
                    'player_budget_id' => $data['player_budget'],
                    'player_parent_id' => $player_parent->id,
                    'has_parent' => true,
                    'graduation_month_year' => $graduation_month_year,
                    'gpa' => $data['player_gpa'],
                    'height' => $height,
                    'other_data' => $other_data
                ]);
        }
    }
}
