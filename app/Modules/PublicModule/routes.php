<?php
        
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\PublicModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All PublicModule routes define here
    Route::prefix('public')->group(function () {

        //TODO whatever not need to authenticate
        Route::get('/players/{user_slug}', 'UsersController@getPlayerProfile')->name('user.players.view');
        Route::get('/coaches/{user_slug}', 'UsersController@getCoachProfile')->name('user.coaches.view');
        Route::get('/business-managers/{user_slug}', 'UsersController@getBusinessManagerProfile')->name('user.business-managers.view');
        Route::get('/parents/{user_slug}', 'UsersController@getParentProfile')->name('user.parents.view');

        Route::get('/schools/{school_slug}', 'SchoolsController@getSchoolProfile')->name('user.schools.view');
        Route::get('/businesses/{business_slug}', 'BusinessesController@getBusinessProfile')->name('user.businesses.view');

        Route::middleware('auth:api')->group(function () {
            //TODO all authenticated users can be access
            Route::get('/home', 'HomeController@index')->name('public.home');

            //TODO only authenticated default users can be access
            Route::middleware('auth.is_default')->group(function () {

            });

            //TODO only authenticated admin users can be access
            Route::middleware('auth.is_admin')->group(function () {

            });

            //TODO only authenticated operator users can be access
            Route::middleware('auth.is_operator')->group(function () {

            });

            //TODO only authenticated player users can be access
            Route::middleware('auth.is_player')->group(function () {
                Route::put('/players/update-basic-info/{user_slug}', 'PlayersController@updateBasicInfo')->name('user.players.update.basic-info');
                Route::put('/players/update-bio/{user_slug}', 'PlayersController@updateBio')->name('user.players.update.bio');
                Route::put('/players/update-other-info/{user_slug}', 'PlayersController@updatePersonalOtherInfo')->name('user.players.update.other-info');
                Route::put('/players/update-budget/{user_slug}', 'PlayersController@updateBudget')->name('user.players.update.budget');
                Route::put('/players/update-core-values/{user_slug}', 'PlayersController@updateCoreValues')->name('user.players.update.core-values');
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
