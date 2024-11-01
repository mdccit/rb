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
                Route::delete('/user-delete/{user_id}', 'UsersController@userAccountDelete')->name('admin.users.delete');
                Route::delete('/user-session-delete/{user_id}', 'UsersController@userSessionDelete')->name('admin.users.delete');

                Route::get('/schools', 'SchoolsController@getAll')->name('admin.schools.get-all');
                Route::get('/schools/{school_id}', 'SchoolsController@get')->name('admin.schools.get');
                Route::delete('/schools/{school_id}', 'SchoolsController@destroySchool')->name('admin.schools.delete');
                Route::post('/school-register', 'SchoolsController@registerSchool')->name('admin.schools.register');
                Route::put('/school-update/{school_id}', 'SchoolsController@updateSchool')->name('admin.schools.update');
                Route::get('/school-view/{school_id}', 'SchoolsController@viewSchool')->name('admin.schools.view');

                Route::get('/schools/users/{school_id}', 'SchoolUsersController@getAllSchoolUsers')->name('admin.schools.users.get-all');
                Route::get('/schools/search-users/{school_id}', 'SchoolUsersController@searchUsers')->name('admin.schools.users.search');
                Route::post('/schools/add-user', 'SchoolUsersController@addSchoolUser')->name('admin.schools.users.add');

                Route::get('/businesses', 'BusinessesController@getAll')->name('admin.businesses.get-all');
                Route::get('/businesses/{business_id}', 'BusinessesController@get')->name('admin.businesses.get');
                Route::delete('/businesses/{business_id}', 'BusinessesController@destroyBusiness')->name('admin.businesses.delete');
                Route::post('/business-register', 'BusinessesController@registerBusiness')->name('admin.businesses.register');
                Route::put('/business-update/{business_id}', 'BusinessesController@updateBusiness')->name('admin.businesses.update');
                Route::get('/business-view/{business_id}', 'BusinessesController@viewBusiness')->name('admin.businesses.view');

                Route::get('/businesses/users/{business_id}', 'BusinessUsersController@getAllBusinessUsers')->name('admin.businesses.users.get-all');
                Route::get('/businesses/search-users/{business_id}', 'BusinessUsersController@searchUsers')->name('admin.businesses.users.search');
                Route::post('/businesses/add-user', 'BusinessUsersController@addBusinessUser')->name('admin.businesses.users.add');

                //resource category
                Route::get('/resource-categories', 'ResourceCategoriesController@index')->name('admin.resources-category.index');
                Route::post('/resource-categories-create', 'ResourceCategoriesController@storeCategory')->name('admin.resources-category.create');
                Route::put('/resource-categories-update/{id}', 'ResourceCategoriesController@updateCategory')->name('admin.resources-category.update');
                Route::delete('/resource-categories-delete/{id}', 'ResourceCategoriesController@destroyCategory')->name('admin.resources-category.delete');
                
                //resource
                Route::get('/resource', 'ResourceController@index')->name('admin.resources.index');
                Route::post('/resource-create', 'ResourceController@store')->name('admin.resources.create');
                Route::put('/resource-update/{id}', 'ResourceController@update')->name('admin.resources.update');
                Route::delete('/resource-delete/{id}', 'ResourceController@destroy')->name('admin.resources.delete');
                Route::put('/user-update/{user_id}', 'UsersController@updateUser')->name('admin.users.update');

                Route::get('/transfer-player', 'TransferPlayerController@getAllUsers')->name('admin.transfer.get-all');
                Route::post('/transfer-player-register', 'TransferPlayerController@store')->name('admin.transfer.register');
                Route::put('/transfer-player-update/{id}', 'TransferPlayerController@update')->name('admin.transfer.update');
                Route::delete('/transfer-player-delete/{id}', 'TransferPlayerController@destory')->name('admin.transfer.delete');


                //player
                Route::get('/player-get/{id}', 'PlayerController@getUser')->name('admin.player.index');
                Route::put('/player-update/{id}', 'PlayerController@updateUser')->name('admin.player.update');

                //morderation 
                Route::get('/morderation-get-all', 'ModerationRequestController@getAll')->name('admin.morderation.get-all');
                Route::get('/morderation-get/{id}', 'ModerationRequestController@get')->name('admin.morderation.get');
                Route::put('/morderation-close/{id}', 'ModerationRequestController@close')->name('admin.morderation.close');
                Route::put('/morderation-reopen/{id}', 'ModerationRequestController@reopen')->name('admin.morderation.reopen');
                Route::delete('/morderation-delete/{id}', 'ModerationRequestController@delete')->name('admin.morderation.delete');
                Route::get('/morderation-comment-get-all/{id}', 'ModerationCommentController@getAll')->name('admin.morderation-comment.get-all');
                Route::post('/morderation-comment-create', 'ModerationCommentController@store')->name('admin.morderation-comment.store');
                Route::put('/morderation-approve/{id}', 'ModerationRequestController@userApprove')->name('admin.morderation.approve');
                Route::get('/morderation-log/{id}', 'ModerationRequestController@getAllModerationLog')->name('admin.morderation.log');
                Route::get('/morderation-open-count', 'ModerationRequestController@getAllModerationOpenCount')->name('admin.morderation.open-count');

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
