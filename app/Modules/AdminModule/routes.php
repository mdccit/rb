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
                Route::get('/schools/{school_id}', 'SchoolsController@get')->name('admin.schools.get');
                Route::post('/school-register', 'SchoolsController@registerSchool')->name('admin.schools.register');
                Route::put('/school-update/{school_id}', 'SchoolsController@updateSchool')->name('admin.schools.update');

                Route::get('/schools/users/{school_id}', 'SchoolUsersController@getAllSchoolUsers')->name('admin.schools.users.get-all');
                Route::get('/schools/search-users/{school_id}', 'SchoolUsersController@searchUsers')->name('admin.schools.users.search');
                Route::post('/schools/add-user', 'SchoolUsersController@addSchoolUser')->name('admin.schools.users.add');
                
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
