<?php
        
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\AuthModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','locale','cors', 'json.response']], function() {

    //TODO All AuthModule routes define here
    Route::prefix('auth')->group(function () {

        //TODO All routes that are required access key
        Route::middleware('access.key')->group(function () {
            //TODO whatever not need to authenticate
            Route::post('/register', 'AuthController@authRegister')->name('auth.register');
            Route::post('/login', 'AuthController@authLogin')->name('auth.login');

            Route::get('/google-auth-url', 'GoogleAuthController@getAuthUrl')->name('auth.google.url');
            Route::post('/google-register', 'GoogleAuthController@authRegister')->name('auth.google.register');
            Route::post('/google-login', 'GoogleAuthController@authLogin')->name('auth.google.login');

            Route::post('/forgot-password-request', 'ForgotPasswordController@forgotPasswordRequest')->name('auth.forgot-password.request');
            Route::put('/reset-password/{password_reset_id}', 'ForgotPasswordController@passwordReset')->name('auth.forgot-password.reset');

            Route::middleware('auth:api')->group(function () {
                //TODO whatever need to authenticate
                Route::put('/logout', 'AuthController@authLogout')->name('auth.logout');

                Route::get('/browser-other-tokens-logout', 'BrowserSessionController@logOutOtherBrowserSession')->name('auth.browser-session.logout');
                //update password
                Route::post('/update-password', 'UpdatePasswordController@updatePassword')->name('auth.password-update');

                //TODO only authenticated default users can be access
                Route::middleware('auth.is_default')->group(function () {
                    Route::put('/player-register', 'RegisterController@playerRegister')->name('auth.player.register');
                    Route::put('/coach-register', 'RegisterController@coachRegister')->name('auth.coach.register');
                    Route::put('/business-manager-register', 'RegisterController@businessManagerRegister')->name('auth.business-manager.register');
                    Route::put('/parent-register', 'RegisterController@parentRegister')->name('auth.parent.register');
                });

            });
        });

        //TODO All routes that are not required access key
        Route::get('email/verify/{id}/{hash}', 'AuthController@verifyEmail')->name('verification.verify'); // Make sure to keep this as your route name
        Route::get('email/resend/{id}', 'AuthController@resendVerificationEmail')->name('verification.resend');

    });
});
