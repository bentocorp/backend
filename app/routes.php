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
 * Authenticated Users Only Routes
 */
#Route::group(array('before' => 'auth', 'namespace' => 'Bento\Ctrl'), function() {
Route::group(array('namespace' => 'Bento\Ctrl'), function() {

    Route::get('menu/{date}', 'MenuCtrl@show');

});





/**
 * Admins Only Routes
 * 
 * prefix => admin: All routes accessed through /admin/{...}
 * before => admin: Calling the admin filter on all routes.
 */
Route::group(array('prefix' => 'admin', 'before' => 'admin'), function() {

    // admin routes here

});


