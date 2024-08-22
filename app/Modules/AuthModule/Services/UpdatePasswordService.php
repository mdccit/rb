<?php


namespace App\Modules\AuthModule\Services;


use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordService
{
    public function updatePassword(array $data){

        User::connect(config('database.default'))
            ->where('id', auth()->id())
            ->update([
                'password' => Hash::make($data['password'])
            ]);
    }
}
