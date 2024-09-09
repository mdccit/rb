<?php


namespace App\Modules\PublicModule\Services;


use App\Models\User;

class PlayerService
{
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
                'bio' => $data['bio'],
            ]);
        }
    }
}
