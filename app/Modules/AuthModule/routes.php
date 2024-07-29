<?php
        
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\AuthModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All AuthModule routes define here
    Route::prefix('auth')->group(function () {

        //TODO whatever not need to authenticate
        Route::post('/register', 'AuthController@authRegister')->name('auth.register');
        Route::post('/login', 'AuthController@authLogin')->name('auth.register');

        Route::middleware('auth:api')->group(function () {
            //TODO whatever need to authenticate
            Route::put('/logout', 'AuthController@authLogout')->name('auth.logout');
        });

    });
});
