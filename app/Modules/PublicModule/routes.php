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
                Route::put('/coaches/update-basic-info/{user_slug}', 'CoachesController@updateBasicInfo')->name('user.coaches.update.basic-info');
                Route::put('/coaches/update-bio/{user_slug}', 'CoachesController@updateBio')->name('user.coaches.update.bio');
                Route::put('/coaches/update-other-info/{user_slug}', 'CoachesController@updatePersonalOtherInfo')->name('user.coaches.update.other-info');

                //school team
                Route::get('/school-team-get/{school_id}', 'SchoolsTeamController@getSchoolTeam')->name('school.team.get');
                Route::get('/school-team-info/{team_id}', 'SchoolsTeamController@getSchoolTeamInfo')->name('school.team.info');
                Route::post('/school-team-add', 'SchoolsTeamController@createSchoolTeam')->name('school.team.add');
                Route::delete('/school-team-delete', 'SchoolsTeamController@destroy')->name('school.team.delete');
                
                //school user delete
                Route::delete('/schools-user-delete/{id}', 'SchoolsController@destroy')->name('user.schools.user-delete');


                Route::put('/schools/update-basic-info/{school_slug}', 'SchoolsController@updateBasicInfo')->name('user.schools.update.basic-info');
                Route::put('/schools/update-bio/{school_slug}', 'SchoolsController@updateBio')->name('user.schools.update.bio');
                Route::put('/schools/add-new-academic/{school_slug}', 'SchoolsController@addNewAcademic')->name('user.schools.add-new.academic');
                Route::put('/schools/remove-academic/{school_slug}', 'SchoolsController@removeAcademic')->name('user.schools.remove.academic');
                Route::put('/schools/update-tennis-info/{school_slug}', 'SchoolsController@updateTennisInfo')->name('user.schools.update.tennis-info');
                Route::put('/schools/update-status-info/{school_slug}', 'SchoolsController@updateStatusInfo')->name('user.schools.update.status-info');
            });

            //TODO only authenticated business manager users can be access
            Route::middleware('auth.is_business_manager')->group(function () {
                Route::put('/business-managers/update-basic-info/{user_slug}', 'BusinessManagersController@updateBasicInfo')->name('user.business-managers.update.basic-info');
                Route::put('/business-managers/update-bio/{user_slug}', 'BusinessManagersController@updateBio')->name('user.business-managers.update.bio');
                Route::put('/business-managers/update-other-info/{user_slug}', 'BusinessManagersController@updatePersonalOtherInfo')->name('user.business-managers.update.other-info');

                Route::put('/businesses/update-basic-info/{business_slug}', 'BusinessesController@updateBasicInfo')->name('user.businesses.update.basic-info');
                Route::put('/businesses/update-bio/{business_slug}', 'BusinessesController@updateBio')->name('user.businesses.update.bio');
            });

            //TODO only authenticated parent users can be access
            Route::middleware('auth.is_parent')->group(function () {
                Route::put('/parents/update-basic-info/{user_slug}', 'ParentsController@updateBasicInfo')->name('user.parents.update.basic-info');
                Route::put('/parents/update-bio/{user_slug}', 'ParentsController@updateBio')->name('user.parents.update.bio');
                Route::put('/parents/update-other-info/{user_slug}', 'ParentsController@updatePersonalOtherInfo')->name('user.parents.update.other-info');
            });
        });

    });
});
