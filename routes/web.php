<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get( '/to/{hash}', 'UrlsController@toUrl');

Route::middleware('sop')->get('sop-admin', function(){
    ob_start();
    require(public_path()."/sop/index.html");
    return ob_get_clean();
});

//Route::get('/land', 'API\Products@newLand');


Auth::routes();

