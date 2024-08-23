<?php


namespace App\Modules\AdminModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Country;
use App\Models\Player;
use App\Models\User;
use App\Models\UserPhone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlayerService
{
    

    public function getUser ($user_id){
        $user = User::connect(config('database.secondary'))
            ->join('players', 'players.user_id', '=' ,'users.id')
            ->where('users.id', $user_id)
            ->select(
                'users.id',
                'users.gender',
                'users.date_of_birth',
                'players.graduation_month_year',
                'players.gpa',
                // 'players.nationality',
                'players.height',
                'players.weight',
                'players.other_data'
            )
            ->first();

        return $user;
    }

   
    public function updateUser(array $data, $user_id){
        $user = User::connect(config('database.default'))
            ->where('id', $user_id)
            ->first();

        $user->update([
            'gender' =>isset($data['gender']) ? $data['gender'] : null,
            'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : null,
        ]);
        
        //Player
        $player = Player::connect(config('database.default'))
                    ->where('user_id', $user->id)->first();
        $player->update([
            'graduation_month_year' =>isset($data['graduation_month_year']) ? $data['graduation_month_year'] : null,
            'gpa' => isset($data['gpa']) ? $data['gpa'] : null,
            'height' => isset($data['height_cm']) ? $data['height_cm'] : null,
            'weight' => isset($data['weight']) ? $data['weight'] : null,
            'other_data' => isset($data['other_data']) ? $data['other_data'] : null,
        ]);
    }

}
