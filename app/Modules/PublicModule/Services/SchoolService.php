<?php


namespace App\Modules\PublicModule\Services;


use App\Models\School;
use App\Models\SchoolUser;

class SchoolService
{
    public function getSchoolProfile ($school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
            ->select(
                'id',
                'name',
                'bio',
                'slug',
                'is_verified',
                'is_approved',
                'gov_id',
                'gov_sync_settings',
                'url',
                'genders_recruiting',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $school_users = SchoolUser::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'school_users.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->where('school_users.school_id', $school->id)
            ->select(
                'school_users.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'users.slug',
                'user_roles.name as user_role',
                'school_users.role as school_user_role'
            )
            ->get();

        return [
            'school_info' => $school,
            'school_users_info' => $school_users,
        ];
    }

    public function destroy($id){
        SchoolUser::connect(config('database.default'))->destroy($id);
    }
}
