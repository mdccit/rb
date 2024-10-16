<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\SubscriptionModule\Controllers', 'prefix' => 'api/' . config('app.version'), 'middleware' => ['api', 'locale', 'cors', 'json.response']], function () {

  //TODO All AuthModule routes define here
  Route::prefix('subscription')->group(function () {

    //TODO All routes that are required access key
    Route::middleware('access.key')->group(function () {

      // Routes for subscription retrieval by user ID and for all subscriptions
      Route::get('/single', 'SubscriptionController@getSubscriptionByUserId')->name('subscription.getByUserId');
      Route::get('/all', 'SubscriptionController@getAllSubscriptions')->name('subscription.getAll');

      Route::middleware('auth:api')->group(function () {

        // Subscription-related routes
        Route::get('/show', 'SubscriptionController@show')->name('subscription.show'); // Show user's subscription
        Route::put('/cancel', 'SubscriptionController@cancel')->name('subscription.cancel'); // Cancel the subscription
        Route::put('/renew', 'SubscriptionController@renew')->name('subscription.renew'); // Renew the subscription if applicable
        Route::delete('/remove-payment-method/{payment_method_id}', 'SubscriptionController@removePaymentMethod')->name('subscription.removePaymentMethod');
        Route::get('/payment-methods', 'SubscriptionController@getCustomerPaymentMethods')->name('subscription.paymentmethods'); 
        Route::get('/active-payment-method', 'SubscriptionController@getSubscriptionPaymentMethod')->name('subscription.paymentmethod'); 

        // Recurring Subscriptions
        Route::post('/recurring/create', 'SubscriptionController@createRecurringSubscription')->name('subscription.createRecurring');


         // New added routes
         Route::put('/update', 'SubscriptionController@update')->name('subscription.update');
         Route::post('/upgrade', 'SubscriptionController@upgrade')->name('subscription.upgrade');
         Route::get('/status', 'SubscriptionController@checkStatus')->name('subscription.checkStatus');
         Route::get('/stripe/invoice-preview', 'SubscriptionController@getUpcomingInvoice')->name('subscription.stripe.invoice.preview');
         Route::put('/update-payment-method', 'SubscriptionController@updatePaymentMethod')->name('subscription.updatePaymentMethod');


        // New Stripe payment-related routes
        Route::get('/stripe/get-stripe-customer-id', 'SubscriptionController@getStripeCustomerId')->name('subscription.getStripeCustomerId');
        Route::get('/stripe/payment-form', 'SubscriptionController@showPaymentForm')->name('subscription.paymentForm');
        Route::post('/stripe/payment', 'SubscriptionController@handlePayment')->name('subscription.handlePayment');
        Route::post('/stripe/create-setup-intent', 'SubscriptionController@createSetupIntent')->name('subscription.createSetupIntent');
        Route::post('/stripe/confirm-setup-intent', 'SubscriptionController@confirmSetupIntent')->name('subscription.confirmSetupIntent');
        Route::post('/stripe/confirm-payment-and-create-subscription', 'SubscriptionController@createSubscription');
        Route::get('/stripe/payment-history', 'SubscriptionController@getPaymentHistoryFromStripe')->name('subscription.stripe.payment.history'); // Renew the subscription if applicable
        Route::get('/stripe/customer-payment-methods', 'SubscriptionController@getCustomerPaymentMethods')->name('subscription.get.customer.payment.methods');
        Route::get('/stripe/customer-active-payment-method', 'SubscriptionController@getSubscriptionPaymentMethod')->name('subscription.get.customer.active.payment.method');

      });
    });
  });


  Route::prefix('stripe')->group(function () {

    //TODO All routes that are required access key
    Route::middleware('access.key')->group(function () {

      Route::get('/all', 'SubscriptionController@showAllSubscriptions')->name('subscriptions.all');

      Route::middleware('auth:api')->group(function () {
        Route::get('/user', 'SubscriptionController@showUserSubscription')->name('subscriptions.user');
      });

    });
  });
});
