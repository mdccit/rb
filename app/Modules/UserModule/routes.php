<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\UserModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All PublicModule routes define here
    Route::prefix('user')->group(function () {

        //TODO whatever not need to authenticate


        Route::middleware('auth:api')->group(function () {
            //TODO all authenticated users can be access
            //Route::post('/user-register', 'UsersController@registerUser')->name('admin.users.register');

            //TODO only authenticated default users can be access
            Route::middleware('auth.is_default')->group(function () {

            });

            //TODO only authenticated admin users can be access
            Route::middleware('auth.is_admin')->group(function () {
                //Route::get('/users', 'UsersController@index')->name('admin.users.index');

            });

            //TODO only authenticated operator users can be access
            Route::middleware('auth.is_operator')->group(function () {

            });

            //TODO only authenticated player users can be access
            Route::middleware('auth.is_player')->group(function () {

            });

            //TODO only authenticated coach users can be access
            Route::middleware('auth.is_coach')->group(function () {

            });

            //TODO only authenticated business manager users can be access
            Route::middleware('auth.is_business_manager')->group(function () {

            });

            //TODO only authenticated parent users can be access
            Route::middleware('auth.is_parent')->group(function () {

            });
        });

    });
});
