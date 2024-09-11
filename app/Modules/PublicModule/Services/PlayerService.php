<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Player;
use App\Models\User;
use Carbon\Carbon;

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
                'country_id' => $data['country'],
                'nationality_id' => $data['nationality'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
            ]);

            $height = $data['height_in_cm']?$data['height_cm']:(($data['height_ft']*12)+$data['height_in'])*2.54;
            $other_data = [
                'utr' => $data['player_utr'],
                'handedness' => $data['player_handedness'],
            ];
            $graduation_month_year = Carbon::createFromFormat('Y-m', $data['player_graduation_month_year']);

            Player::connect(config('database.default'))
                ->where('user_id', $user->id)
                ->update([
                    'graduation_month_year' => $graduation_month_year,
                    'height' => $height,
                ]);

        }
    }
}
