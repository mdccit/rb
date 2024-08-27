<?php


namespace App\Modules\UserModule\Services;


use App\Models\Player;
use App\Models\User;
use App\Models\UserPhone;

class UserService
{
    public function getPlayerProfiles ($user_id){
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
                'users.is_approved',
                'user_roles.id as user_role_id',
                'user_roles.name as user_role',
                'user_types.id as user_type_id',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
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

        $player = Player::connect(config('database.secondary'))
            ->where('user_id', $user_id)
            ->select(
                'has_parent',
                'graduation_month_year',
                'gpa',
                'height',
                'weight',
                'other_data'
            )
            ->first();

        return [
            'user_basic_info' => $user,
            'user_contact_info' => $user_phone,
            'player_info' => $player,
        ];
    }
}
