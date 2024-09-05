<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Business;
use App\Models\BusinessManager;

class BusinessService
{
    public function getBusinessProfile ($business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
            ->select(
                'id',
                'name',
                'bio',
                'slug',
                'is_verified',
                'is_approved',
                'url',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $business_users = BusinessManager::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'business_managers.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->where('business_managers.business_id', $business->id)
            ->select(
                'business_managers.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'users.slug',
                'user_roles.name as user_role',
                'business_managers.type as business_user_role'
            )
            ->get();

        return [
            'business_info' => $business,
            'business_users_info' => $business_users,
        ];
    }
}
