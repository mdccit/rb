<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@qualitapps.com',
                'password' => 'jza36tbM4WMp',
                'user_role' => config('app.user_roles.admin')
            ]
        ];

        foreach($list as $data)
        {
            \App\Models\User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'slug' => Str::uuid(),
                'user_role_id' => $data['user_role'],
                'user_type_id' => config('app.user_types.free'),
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(10),
                'last_logged_at' => Carbon::now(),
            ]);
        }
    }
}
