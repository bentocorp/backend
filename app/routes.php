<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});



/**
 * Authenticated Users Only
 */
Route::group(array('before' => 'auth'), function() {

    Route::controller('menu', 'MenuCtrl');

});





/**
 * Admins Only
 */
Route::group(array('prefix' => 'admin', 'before' => 'admin'), function() {

    // admin routes here

});


