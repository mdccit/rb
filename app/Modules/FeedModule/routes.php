<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\FeedModule\Controllers', 'prefix' => 'api/' . config('app.version'), 'middleware' => ['api', 'access.key', 'locale', 'cors', 'json.response']], function () {

    //TODO All PublicModule routes define here
    Route::prefix('feed')->group(function () {

        Route::get('/posts', 'PostController@index')->name('posts.index');
        Route::get('/posts/{id}', 'PostController@show')->name('posts.show');
   
        Route::delete('/posts/{id}', 'PostController@destroy')->name('posts.destroy');


        Route::delete('/comments/{id}', 'PostController@deleteComment')->name('posts.delete_comment');
        Route::post('/posts/{id}/like', 'PostController@addLike')->name('posts.add_like');
        Route::delete('/posts/{id}/like', 'PostController@removeLike')->name('posts.remove_like');

        Route::middleware('auth:api')->group(function () {
            //TODO all authenticated users can be access
            //Route::post('/user-register', 'UsersController@registerUser')->name('admin.users.register');

            Route::post('/post', 'PostController@store')->name('posts.store');
            Route::put('/posts/{id}', 'PostController@update')->name('posts.update');
            Route::post('/posts/{id}/comment', 'PostController@addComment')->name('posts.add_comment');
            Route::put('/comments/{id}', 'PostController@updateComment')->name('posts.update_comment');

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
