<?php
        
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\CommonModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All AuthModule routes define here
    Route::prefix('common')->group(function () {

        //TODO whatever not need to authenticate
        Route::get('/load-combo-list', 'DefaultDataController@loadComboList')->name('common.load.combo');

        Route::middleware('auth:api')->group(function () {
            //TODO whatever need to authenticate
        });

    });
});
