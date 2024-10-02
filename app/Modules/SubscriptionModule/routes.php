<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\Subscription\Controllers', 'prefix' => 'api/' . config('app.version'), 'middleware' => ['api', 'locale', 'cors', 'json.response']], function () {

  //TODO All AuthModule routes define here
  Route::prefix('subscription')->group(function () {

    //TODO All routes that are required access key
    Route::middleware('access.key')->group(function () {

      // Routes for subscription retrieval by user ID and for all subscriptions
      Route::get('/{userId}', 'SubscriptionController@getSubscriptionByUserId')->name('subscription.getByUserId');
      Route::get('/all', 'SubscriptionController@getAllSubscriptions')->name('subscription.getAll');

      Route::middleware('auth:api')->group(function () {

        // Subscription-related routes
        Route::get('/show', 'SubscriptionController@show')->name('subscription.show'); // Show user's subscription
        Route::post('/create', 'SubscriptionController@store')->name('subscription.store'); // Create a new subscription
        Route::put('/cancel', 'SubscriptionController@cancel')->name('subscription.cancel'); // Cancel the subscription
        Route::put('/renew', 'SubscriptionController@renew')->name('subscription.renew'); // Renew the subscription if applicable

         // New Stripe payment-related routes
         Route::get('/payment-form', 'SubscriptionController@showPaymentForm')->name('subscription.paymentForm'); // Display the Stripe payment form
         Route::post('/payment', 'SubscriptionController@handlePayment')->name('subscription.handlePayment'); // Handle the Stripe payment

      });
    });
  });
});
