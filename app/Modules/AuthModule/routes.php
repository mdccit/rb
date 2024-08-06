<?php
        
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\AuthModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All AuthModule routes define here
    Route::prefix('auth')->group(function () {

        //TODO whatever not need to authenticate
        Route::post('/register', 'AuthController@authRegister')->name('auth.register');
        Route::post('/login', 'AuthController@authLogin')->name('auth.login');

        Route::get('/google-auth-url', 'GoogleAuthController@getAuthUrl')->name('auth.google.url');
        Route::post('/google-register', 'GoogleAuthController@authRegister')->name('auth.google.register');
        Route::post('/google-login', 'GoogleAuthController@authLogin')->name('auth.google.login');

        Route::middleware('auth:api')->group(function () {
            //TODO whatever need to authenticate
            Route::put('/logout', 'AuthController@authLogout')->name('auth.logout');

            Route::put('/player-register', 'RegisterController@playerRegister')->name('auth.player.register');
            Route::put('/coach-register', 'RegisterController@coachRegister')->name('auth.coach.register');
            Route::put('/business-manager-register', 'RegisterController@businessManagerRegister')->name('auth.business-manager.register');
            Route::put('/parent-register', 'RegisterController@parentRegister')->name('auth.parent.register');
        });

    });
});
