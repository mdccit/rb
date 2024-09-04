<?php


namespace App\Modules\PublicModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Player;
use App\Models\PlayerParent;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;

class UserService
{
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
                'users.is_approved',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            )
            ->first();

        $user_phone = null;
        $user_address = null;
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

            $player = Player::connect(config('database.secondary'))
                ->where('user_id', $user->id)
                ->select(
                    'has_parent',
                    'graduation_month_year',
                    'gpa',
                    'height',
                    'weight',
                    'other_data'
                )
                ->first();
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
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
                'users.is_approved',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            )
            ->first();

        $user_phone = null;
        $user_address = null;
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

            $coach = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('coaches.user_id', $user->id)
                ->select(
                    'coaches.id as coach_id',
                    'schools.id as school_id',
                    'coaches.position',
                    'coaches.type',
                    'coaches.status',
                    'schools.name as school_name',
                    'schools.gov_id as school_gov_id',
                    'schools.url as school_url',
                    'schools.genders_recruiting as school_genders_recruiting',
                    'schools.conference_id as school_conference_id',
                    'schools.division_id as school_division_id',
                    'schools.other_data as school_other_data'
                )
                ->first();
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
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
                'users.is_approved',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            )
            ->first();

        $user_phone = null;
        $user_address = null;
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

            $business_manager = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('business_managers.user_id', $user->id)
                ->select(
                    'business_managers.id as business_manager_id',
                    'businesses.id as business_id',
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
                'users.is_approved',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            )
            ->first();

        $user_phone = null;
        $user_address = null;
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
                    ->where('players.has_parent', true)
                    ->where('players.player_parent_id', $parent->id)
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
                        'users.is_approved',
                        'user_roles.id as user_role_id',
                        'user_roles.name as user_role',
                        'user_types.id as user_type_id',
                        'user_types.name as user_type',
                        'users.created_at as joined_at',
                        'users.last_logged_at as last_seen_at'
                    )
                    ->get();
            }
        }

        return [
            'user_basic_info' => $user,
            'user_phone_info' => $user_phone,
            'user_address_info' => $user_address,
            'parent_info' => $parent,
            'child_info' => $childs,
        ];
    }
}
