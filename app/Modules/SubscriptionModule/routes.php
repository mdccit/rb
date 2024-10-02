<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\AuthModule\Controllers', 'prefix' => 'api/' . config('app.version'), 'middleware' => ['api', 'locale', 'cors', 'json.response']], function () {

  //TODO All AuthModule routes define here
  Route::prefix('auth')->group(function () {

    //TODO All routes that are required access key
    Route::middleware('access.key')->group(function () {

      Route::get('/subscription', 'SubscriptionController@show')->name('subscription.show'); // Show user's subscription

      Route::middleware('auth:api')->group(function () {     

        // Subscription-related routes
        Route::post('/subscription', 'SubscriptionController@store')->name('subscription.store'); // Create a new subscription
        Route::put('/subscription/cancel', 'SubscriptionController@cancel')->name('subscription.cancel'); // Cancel the subscription
        Route::put('/subscription/renew', 'SubscriptionController@renew')->name('subscription.renew'); // Renew the subscription if applicable

      });
    });
  });
});
