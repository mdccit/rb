<?php


namespace App\Modules\AdminModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Country;
use App\Models\Player;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserPhone;
use App\Traits\GeneralHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ModerationRequest;

class UserService
{
    use GeneralHelpers;

    public function getAllUsers (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $user_role = array_key_exists("user_role",$data)?$data['user_role']:0;
        $is_email_verified = array_key_exists("is_email_verified",$data)?$data['is_email_verified']:'none';
        $last_seen_at = array_key_exists("last_seen_at",$data)?$data['last_seen_at']:null;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.id', '!=', auth()->user()->id) //Not included himself/herself
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.email',
                'users.slug',
                'user_roles.name as user_role',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            );

        if($user_role != 0){
            $query->where('users.user_role_id', $user_role);
        }

        if ($is_email_verified === 'verified') {
            $query->whereNotNull('users.email_verified_at');
        } elseif ($is_email_verified === 'not_verified') {
            $query->whereNull('users.email_verified_at');
        }

        if ($last_seen_at != null) {
            $query->where('users.last_logged_at', '>=', now()->subDays($last_seen_at));
        }

        if ($search_key != null) {
            $query->where('users.display_name', 'LIKE', '%' . $search_key . '%');
        }


        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;

    }

    public function getUser ($user_id){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.id', $user_id)
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.other_names',
                'users.display_name',
                'users.email',
                'users.slug',
                'users.bio',
                'users.date_of_birth',
                'users.gender',
                'users.nationality_id',
                'users.is_approved',
                'users.is_first_login',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.email_verified_at',
                'users.last_logged_at as last_seen_at',
            )
            ->first();

        $user_phone = UserPhone::connect(config('database.secondary'))
            ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
            ->where('user_phones.user_id', $user_id)
            ->where('user_phones.is_default', true)
            ->select(
                'user_phones.id',
                'user_phones.phone_code',
                'user_phones.phone_number',
                'countries.id as country_id',
                'countries.name as country'
            )
            ->first();

        return [
            'user_basic_info' => $user,
            'user_contact_info' => $user_phone,
        ];
    }

    public function createUser(array $data){
        $user = User::connect(config('database.default'))
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'slug' => $this->generateSlug(new User(), $data['first_name'].' '.$data['last_name'], 'slug'),
                'user_role_id' => $data['user_role'],
                'user_type_id' => config('app.user_types.free'),
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(10)
            ]);

        // Create moderation reques
        ModerationRequest::create([
            'moderatable_type' => User::class,
            'moderatable_id' => $user->id,
            'priority' => 'medium',
            'created_by' => auth()->id(),
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

        if($data['is_set_email_verified']){
            User::connect(config('database.default'))
                ->where('id', $user->id)
                ->update([
                    'email_verified_at' => Carbon::now(),
                ]);
        }else{
            $user->sendEmailVerificationNotification();
        }

        $sport = Sport::connect(config('database.secondary'))->first();

        //Player
        if($data['user_role'] == config('app.user_roles.player')){
            $player = Player::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$player){
                Player::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                        'sport_id' => $sport->id,
                    ]);
            }
        }

        //Coach
        if($data['user_role'] == config('app.user_roles.coach')){
            $coach = Coach::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$coach){
                Coach::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                        'sport_id' => $sport->id,
                    ]);
            }
        }

        //Business Manager
        if($data['user_role'] == config('app.user_roles.business_manager')){
            $business_manager = BusinessManager::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$business_manager){
                BusinessManager::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        }

    }

    public function updateUser(array $data, $user_id){
        $user = User::connect(config('database.default'))
            ->where('id', $user_id)
            ->first();
        if($user){
            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'other_names' => $data['other_names'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'is_approved' => $data['is_approved'],
            ]);

            if ($data['email'] !== $user->email) {
                $user->update([
                    'email' => $data['email'],
                    'email_verified_at' => null,
                ]);
            }

            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password'])
                ]);
            }

            if($data['is_set_email_verified']){
                $user->update([
                    'email_verified_at' => Carbon::now(),
                ]);
            }

            $user_phone = UserPhone::connect(config('database.default'))
                ->where('user_id', $user_id)->first();
            if($user_phone){
                $phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
                $user_phone->update([
                    'user_id' => $user_id,
                    'country_id' => $data['phone_code_country'],
                    'is_default' => true,
                    'phone_code' => $phone_code,
                    'phone_number' => $data['phone_number'],
                ]);
            }


            if ($data['user_role'] !== $user->user_role_id) {
                $user->update([
                    'user_role_id' => $data['user_role'],
                ]);

                //Player
                if($data['user_role'] == config('app.user_roles.player')){
                    $player = Player::connect(config('database.secondary'))
                        ->where('user_id', $user->id)->first();
                    if(!$player){
                        Player::connect(config('database.default'))
                            ->create([
                                'user_id' => $user->id,
                            ]);
                    }
                }

                //Coach
                if($data['user_role'] == config('app.user_roles.coach')){
                    $coach = Coach::connect(config('database.secondary'))
                        ->where('user_id', $user->id)->first();
                    if(!$coach){
                        Coach::connect(config('database.default'))
                            ->create([
                                'user_id' => $user->id,
                            ]);
                    }
                }

                //Business Manager
                if($data['user_role'] == config('app.user_roles.business_manager')){
                    $business_manager = BusinessManager::connect(config('database.secondary'))
                        ->where('user_id', $user->id)->first();
                    if(!$business_manager){
                        BusinessManager::connect(config('database.default'))
                            ->create([
                                'user_id' => $user->id,
                            ]);
                    }
                }
            }
        }
    }

    public function userDelete($user_id){
        
        User::connect(config('database.default'))->destroy($user_id);
       
    }

    public function userSessionDelete($user_id){
        
        $user = User::connect(config('database.default'))->find($user_id);
        $tokens = $user->tokens;

        foreach($tokens as $token){
            $token->revoke();
        }
       
    }


}
