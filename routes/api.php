<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::namespace('API')->middleware('cors')->group(function (){
    Route::get('xml', 'Feeds@getList');
    Route::get('xml/{id}', 'Feeds@getFeed');
    Route::get('new', 'Feeds@newFeed');
    Route::match(['delete', 'options'],'xml/{id}', 'Feeds@deleteFeed');
    Route::match(['post', 'options'],'xml', 'Feeds@addFeed');
    Route::get('sop', 'Feeds@getOptions');
    Route::match(['post', 'options'],'sop', 'Feeds@saveOptions');

    Route::get('model', 'Offers@getModelOffers');
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
