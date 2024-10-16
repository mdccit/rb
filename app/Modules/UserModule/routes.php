<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Modules\UserModule\Controllers','prefix' => 'api/'.config('app.version'), 'middleware' => ['api','access.key','locale','cors', 'json.response']], function() {

    //TODO All UserModule routes define here
    Route::prefix('user')->group(function () {

        //TODO whatever not need to authenticate


        Route::middleware('auth:api')->group(function () {
            //TODO all authenticated users can be access
            //Route::post('/user-register', 'UsersController@registerUser')->name('admin.users.register');

            //resource
            Route::get('/resource', 'ResourceController@index')->name('user.resources.index');
            Route::get('/resource-category', 'ResourceController@getAllCategories')->name('user.resources.categoru');

            Route::get('/players/{user_id}', 'UsersController@getPlayerProfile')->name('user.players.view');
            Route::get('/coaches/{user_id}', 'UsersController@getCoachProfile')->name('user.coaches.view');
            Route::get('/business-managers/{user_id}', 'UsersController@getBusinessManagerProfile')->name('user.business-managers.view');
            Route::get('/parents/{user_id}', 'UsersController@getParentProfile')->name('user.parents.view');

            //connections
            Route::post('/connections-request', 'ConnectionController@requestConnection')->name('connections.connect.request');
            Route::put('/connections-accept/{id}', 'ConnectionController@connectionAccept')->name('connections.connect.accept');
            Route::put('/connections-cancelle/{id}', 'ConnectionController@connectionCancell')->name('connections.connect.cancelle');
            Route::put('/connections-reject/{id}', 'ConnectionController@connectionReject')->name('connections.connect.reject');
            Route::put('/connections-remove/{id}', 'ConnectionController@connectionRemove')->name('connections.connect.remove');
            Route::get('/connections-list', 'ConnectionController@userinivitationAndConnectedList')->name('connections.connect.connection-list');
            Route::get('/connections-list-with-compare/{id}', 'ConnectionController@connectionList')->name('connections.connect.connection-list-with-compare');
            Route::get('/connections-check/{id}', 'ConnectionController@checkConnectionType')->name('connections.connect.check');

            //conversation
            Route::post('/create-conversation', 'ConversationController@createConversation')->name('user.conversation.create');
            Route::put('/delete-conversation/{id}', 'ConversationController@deleteConversation')->name('user.conversation.delete');
            Route::get('/get-all-conversation', 'ConversationController@getAllConversation')->name('user.conversation.get');

            //chat
             Route::post('/send-message', 'ChatController@sendMessage')->name('chat.message.send');
             Route::get('/get-message/{id}', 'ChatController@getMessages')->name('chat.message.get');
             Route::get('/get-message-unread-count', 'ChatController@unreadMessageCount')->name('chat.message.unread-count');

            //search
            Route::get('/search', 'SearchController@search')->name('user.search.search');
            Route::get('/recent-search', 'SearchController@getRecentSearch')->name('user.search.recent');
            Route::post('/save-search', 'SearchController@saveSearch')->name('user.search.save');
            Route::get('/get-save-search', 'SearchController@getSaveSearch')->name('user.search.get');
            Route::delete('/delete-search/{id}', 'SearchController@deleteSaveSearch')->name('user.search.delete');

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
                Route::get('/transcript/get-transcript', 'TranscriptController@getTranscript')->name('user.transcript.get');
                Route::post('/transcript/create-transcript', 'TranscriptController@createTranscript')->name('user.transcript.create');
                Route::delete('/transcript/delete-transcript/{id}', 'TranscriptController@deleteTranscript')->name('user.transcript.delete');
            });

            //TODO only authenticated coach users can be access
            Route::middleware('auth.is_coach')->group(function () {
                //get transfer player
                Route::get('/transfer-players', 'TransferPlayerController@getAllUsers')->name('user.transfer.get-all');

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
