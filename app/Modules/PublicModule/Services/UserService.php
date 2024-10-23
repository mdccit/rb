<?php


namespace App\Modules\PublicModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Media;
use App\Models\Player;
use App\Models\PlayerParent;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;
use App\Traits\AzureBlobStorage;
use Illuminate\Support\Facades\DB;

class UserService
{
    use AzureBlobStorage;

    public function getUserProfile ($user_slug){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.slug', $user_slug)
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
                'users.country_id',
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
            ->addSelect(DB::raw('IF((SELECT name as countries FROM countries WHERE id = users.country_id ) IS NULL,NULL,(SELECT name FROM countries WHERE id = users.country_id )) as country'))
            ->first();

        $user_phone = null;
        $user_address = null;
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $profile_info = null;
        $children_info = array();
        if($user) {
            $user_phone = UserPhone::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=', 'user_phones.country_id')
                ->where('user_phones.user_id', $user->id)
                ->where('user_phones.is_default', true)
                ->select(
                    'user_phones.id',
                    'user_phones.phone_code',
                    'user_phones.phone_number',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $user_address = UserAddress::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=', 'user_addresses.country_id')
                ->where('user_addresses.user_id', $user->id)
                ->where('user_addresses.is_default', true)
                ->select(
                    'user_addresses.id',
                    'user_addresses.address_line_1',
                    'user_addresses.address_line_2',
                    'user_addresses.city',
                    'user_addresses.state_province',
                    'user_addresses.postal_code',
                    'user_addresses.type',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($user->id,'user_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            switch ($user->user_role_id) {
                case config('app.user_roles.player'):
                    $profile_info = Player::connect(config('database.secondary'))
                        ->join('sports', 'sports.id', '=' ,'players.sport_id')
                        ->where('players.user_id', $user->id)
                        ->select(
                            'players.has_parent',
                            'players.graduation_month_year',
                            'players.gpa',
                            'players.height',
                            'players.weight',
                            'players.other_data',
                            'sports.id as sport_id',
                            'sports.name as sport_name'
                        )
                        ->first();
                    break;
                case config('app.user_roles.coach'):
                    $profile_info = Coach::connect(config('database.secondary'))
                        ->join('sports', 'sports.id', '=' ,'coaches.sport_id')
                        ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                        ->where('coaches.user_id', $user->id)
                        ->select(
                            'coaches.id as coach_id',
                            'schools.id as school_id',
                            'schools.slug as school_slug',
                            'coaches.position',
                            'coaches.type',
                            'coaches.status',
                            'schools.name as school_name',
                            'schools.gov_id as school_gov_id',
                            'schools.url as school_url',
                            'schools.genders_recruiting as school_genders_recruiting',
                            'schools.conference_id as school_conference_id',
                            'schools.division_id as school_division_id',
                            'schools.other_data as school_other_data',
                            'sports.id as sport_id',
                            'sports.name as sport_name'
                        )
                        ->first();
                        $profile_info->school_profile_picture= null;
                        if($profile_info != null){
                            $profile_info->school_profile_picture = $this->getSingleFileByEntityId($profile_info->school_id,'school_profile_picture');
                        }
                       
                    break;
                case config('app.user_roles.business_manager'):
                    $profile_info = BusinessManager::connect(config('database.secondary'))
                        ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                        ->where('business_managers.user_id', $user->id)
                        ->select(
                            'business_managers.id as business_manager_id',
                            'businesses.id as business_id',
                            'businesses.slug as business_slug',
                            'business_managers.position',
                            'business_managers.type',
                            'business_managers.status',
                            'businesses.name as business_name',
                            'businesses.url as business_url',
                            'businesses.other_data as business_other_data'
                        )
                        ->first();
                    break;
                case config('app.user_roles.parent'):
                    $profile_info = PlayerParent::connect(config('database.secondary'))
                        ->where('user_id', $user->id)
                        ->select(
                            'id as parent_id',
                            'child_count'
                        )
                        ->first();

                    if($profile_info){
                        $children_info = Player::connect(config('database.secondary'))
                            ->join('users', 'users.id', '=' ,'players.user_id')
                            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
                            ->join('sports', 'sports.id', '=' ,'players.sport_id')
                            ->where('players.has_parent', true)
                            ->where('players.player_parent_id', $profile_info->parent_id)
                            ->select(
                                'players.id as player_id',
                                'users.id as user_id',
                                'players.has_parent',
                                'players.graduation_month_year',
                                'players.gpa',
                                'players.height',
                                'players.weight',
                                'players.other_data',

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
                                'sports.id as sport_id',
                                'sports.name as sport_name'
                            )
                            ->get();
                        foreach ($children_info as $i => $child_player){
                            $user_phone = UserPhone::connect(config('database.secondary'))
                                ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                                ->where('user_phones.user_id', $child_player->user_id)
                                ->where('user_phones.is_default', true)
                                ->select(
                                    'user_phones.id',
                                    'user_phones.phone_code',
                                    'user_phones.phone_number',
                                    'countries.id as country_id',
                                    'countries.name as country'
                                )
                                ->first();
                            $children_info[$i]['phone_info'] = $user_phone;

                            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');

                            $media_info = [
                                'profile_picture' => $profile_picture,
                            ];

                            $children_info[$i]['media_info'] = $media_info;
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'media_info' => $media_info,
            'profile_info' => $profile_info,
            'children_info' => $children_info,
        ];

    }
    public function getPlayerProfile ($user_slug){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.slug', $user_slug)
            ->where('users.user_role_id', config('app.user_roles.player'))
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
                'users.country_id',
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
            ->addSelect(DB::raw('IF((SELECT name as nationality FROM nationalities WHERE id = users.nationality_id ) IS NULL,NULL,(SELECT name FROM nationalities WHERE id = users.nationality_id )) as nationality'))
            ->addSelect(DB::raw('IF((SELECT name as country FROM countries WHERE id = users.country_id ) IS NULL,NULL,(SELECT name FROM countries WHERE id = users.country_id  )) as country'))

            ->first();

        $user_phone = null;
        $user_address = null;
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $player = null;

        if($user){
            $user_phone = UserPhone::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                ->where('user_phones.user_id', $user->id)
                ->where('user_phones.is_default', true)
                ->select(
                    'user_phones.id',
                    'user_phones.phone_code',
                    'user_phones.phone_number',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();
            
            $user_address = UserAddress::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_addresses.country_id')
                ->where('user_addresses.user_id', $user->id)
                ->where('user_addresses.is_default', true)
                ->select(
                    'user_addresses.id',
                    'user_addresses.address_line_1',
                    'user_addresses.address_line_2',
                    'user_addresses.city',
                    'user_addresses.state_province',
                    'user_addresses.postal_code',
                    'user_addresses.type',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($user->id,'user_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            $player = Player::connect(config('database.secondary'))
                ->join('sports', 'sports.id', '=' ,'players.sport_id')
                ->where('players.user_id', $user->id)
                ->select(
                    'players.has_parent',
                    'players.graduation_month_year',
                    'players.gpa',
                    'players.height',
                    'players.weight',
                    'players.other_data',
                    'sports.id as sport_id',
                    'sports.name as sport_name'
                )
                ->first();
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'media_info' => $media_info,
            'player_info' => $player,
        ];
    }

    public function getCoachProfile ($user_slug){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.slug', $user_slug)
            ->where('users.user_role_id', config('app.user_roles.coach'))
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
                'users.country_id',
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

        $user_phone = null;
        $user_address = null;
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $coach = null;

        if($user){
            $user_phone = UserPhone::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                ->where('user_phones.user_id', $user->id)
                ->where('user_phones.is_default', true)
                ->select(
                    'user_phones.id',
                    'user_phones.phone_code',
                    'user_phones.phone_number',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $user_address = UserAddress::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_addresses.country_id')
                ->where('user_addresses.user_id', $user->id)
                ->where('user_addresses.is_default', true)
                ->select(
                    'user_addresses.id',
                    'user_addresses.address_line_1',
                    'user_addresses.address_line_2',
                    'user_addresses.city',
                    'user_addresses.state_province',
                    'user_addresses.postal_code',
                    'user_addresses.type',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($user->id,'user_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            $coach = Coach::connect(config('database.secondary'))
                ->join('sports', 'sports.id', '=' ,'coaches.sport_id')
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('coaches.user_id', $user->id)
                ->select(
                    'coaches.id as coach_id',
                    'schools.id as school_id',
                    'schools.slug as school_slug',
                    'coaches.position',
                    'coaches.type',
                    'coaches.status',
                    'schools.name as school_name',
                    'schools.gov_id as school_gov_id',
                    'schools.url as school_url',
                    'schools.genders_recruiting as school_genders_recruiting',
                    'schools.conference_id as school_conference_id',
                    'schools.division_id as school_division_id',
                    'schools.other_data as school_other_data',
                    'sports.id as sport_id',
                    'sports.name as sport_name'
                )
                ->first();
                $coach->school_profile_picture= null;
                if($coach != null){
                    $coach->school_profile_picture = $this->getSingleFileByEntityId($coach->school_id,'school_profile_picture');
                }
          
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'media_info' => $media_info,
            'coach_info' => $coach,
        ];
    }

    public function getBusinessManagerProfile ($user_slug){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.slug', $user_slug)
            ->where('users.user_role_id', config('app.user_roles.business_manager'))
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
                'users.country_id',
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

        $user_phone = null;
        $user_address = null;
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $business_manager = null;

        if($user){
            $user_phone = UserPhone::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                ->where('user_phones.user_id', $user->id)
                ->where('user_phones.is_default', true)
                ->select(
                    'user_phones.id',
                    'user_phones.phone_code',
                    'user_phones.phone_number',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $user_address = UserAddress::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_addresses.country_id')
                ->where('user_addresses.user_id', $user->id)
                ->where('user_addresses.is_default', true)
                ->select(
                    'user_addresses.id',
                    'user_addresses.address_line_1',
                    'user_addresses.address_line_2',
                    'user_addresses.city',
                    'user_addresses.state_province',
                    'user_addresses.postal_code',
                    'user_addresses.type',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($user->id,'user_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            $business_manager = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('business_managers.user_id', $user->id)
                ->select(
                    'business_managers.id as business_manager_id',
                    'businesses.id as business_id',
                    'businesses.slug as business_slug',
                    'business_managers.position',
                    'business_managers.type',
                    'business_managers.status',
                    'businesses.name as business_name',
                    'businesses.url as business_url',
                    'businesses.other_data as business_other_data'
                )
                ->first();
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'media_info' => $media_info,
            'business_manager_info' => $business_manager,
        ];
    }

    public function getParentProfile ($user_slug){
        $user = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.slug', $user_slug)
            ->where('users.user_role_id', config('app.user_roles.parent'))
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
                'users.country_id',
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

        $user_phone = null;
        $user_address = null;
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $parent = null;
        $childs = array();

        if($user){
            $user_phone = UserPhone::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                ->where('user_phones.user_id', $user->id)
                ->where('user_phones.is_default', true)
                ->select(
                    'user_phones.id',
                    'user_phones.phone_code',
                    'user_phones.phone_number',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $user_address = UserAddress::connect(config('database.secondary'))
                ->join('countries', 'countries.id', '=' ,'user_addresses.country_id')
                ->where('user_addresses.user_id', $user->id)
                ->where('user_addresses.is_default', true)
                ->select(
                    'user_addresses.id',
                    'user_addresses.address_line_1',
                    'user_addresses.address_line_2',
                    'user_addresses.city',
                    'user_addresses.state_province',
                    'user_addresses.postal_code',
                    'user_addresses.type',
                    'countries.id as country_id',
                    'countries.name as country'
                )
                ->first();

            $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($user->id,'user_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            $parent = PlayerParent::connect(config('database.secondary'))
                ->where('user_id', $user->id)
                ->select(
                    'id as parent_id',
                    'child_count'
                )
                ->first();

            if($parent){
                $childs = Player::connect(config('database.secondary'))
                    ->join('users', 'users.id', '=' ,'players.user_id')
                    ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                    ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
                    ->join('sports', 'sports.id', '=' ,'players.sport_id')
                    ->where('players.has_parent', true)
                    ->where('players.player_parent_id', $parent->parent_id)
                    ->select(
                        'players.id as player_id',
                        'users.id as user_id',
                        'players.has_parent',
                        'players.graduation_month_year',
                        'players.gpa',
                        'players.height',
                        'players.weight',
                        'players.other_data',

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
                        'users.country_id',
                        'users.is_approved',
                        'users.is_first_login',
                        'user_roles.id as user_role_id',
                        'user_roles.name as user_role',
                        'user_types.id as user_type_id',
                        'user_types.name as user_type',
                        'users.created_at as joined_at',
                        'users.email_verified_at',
                        'users.last_logged_at as last_seen_at',
                        'sports.id as sport_id',
                        'sports.name as sport_name'
                    )
                    ->get();

                foreach ($childs as $i => $child_player){
                    $user_phone = UserPhone::connect(config('database.secondary'))
                        ->join('countries', 'countries.id', '=' ,'user_phones.country_id')
                        ->where('user_phones.user_id', $child_player->user_id)
                        ->where('user_phones.is_default', true)
                        ->select(
                            'user_phones.id',
                            'user_phones.phone_code',
                            'user_phones.phone_number',
                            'countries.id as country_id',
                            'countries.name as country'
                        )
                        ->first();
                    $childs[$i]['phone_info'] = $user_phone;

                    $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');

                    $media_info = [
                        'profile_picture' => $profile_picture,
                    ];

                    $childs[$i]['media_info'] = $media_info;
                }
            }
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'media_info' => $media_info,
            'parent_info' => $parent,
            'child_info' => $childs,
        ];
    }
}
