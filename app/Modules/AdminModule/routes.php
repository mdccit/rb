<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\AdminModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All PublicModule routes define here
    Route::prefix('admin')->group(function () {

        //TODO whatever not need to authenticate


        Route::middleware('auth:api')->group(function () {
            //TODO all authenticated users can be access
            //Route::get('/home', 'HomeController@index')->name('public.home');

            //TODO only authenticated default users can be access
            Route::middleware('auth.is_default')->group(function () {

            });

            //TODO only authenticated admin users can be access
            Route::middleware('auth.is_admin')->group(function () {
                Route::get('/users', 'UsersController@getAll')->name('admin.users.get-all');
                Route::get('/users/{user_id}', 'UsersController@get')->name('admin.users.get');
                Route::post('/user-register', 'UsersController@registerUser')->name('admin.users.register');
                Route::put('/user-update/{user_id}', 'UsersController@updateUser')->name('admin.users.update');

                Route::get('/schools', 'SchoolsController@getAll')->name('admin.schools.get-all');
                Route::post('/school-register', 'SchoolsController@registerSchool')->name('admin.schools.register');
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
