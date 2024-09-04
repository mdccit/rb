<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasUuids;

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'other_names',
        'display_name',
        'email',
        'password',
        'provider_id',
        'provider_name',
        'google_access_token_json',
        'user_role_id',
        'user_type_id',
        'country_id',
        'nationality_id',
        'slug',
        'gender',
        'date_of_birth',
        'email_verified_at',
        'last_logged_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function getUserRole()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    public function getUserType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * Check current user's user role
     *
     */
    public function isDefaultUser(){
        return $this->user_role_id === config('app.user_roles.default');
    }

    public function isAdmin(){
        return $this->user_role_id === config('app.user_roles.admin');
    }

    public function isOperator(){
        return $this->user_role_id === config('app.user_roles.operator');
    }

    public function isPlayer(){
        return $this->user_role_id === config('app.user_roles.player');
    }

    public function isCoach(){
        return $this->user_role_id === config('app.user_roles.coach');
    }

    public function isBusinessManager(){
        return $this->user_role_id === config('app.user_roles.business_manager');
    }

    public function isParent(){
        return $this->user_role_id === config('app.user_roles.parent');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification($this));
    }

    public function conversation()
    {
        return $this->hasMany(Conversation::class);
    }

}
