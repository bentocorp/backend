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

// Boostrapping (comment out when done)
Route::group(array('namespace' => 'Bento\Ctrl'), function() {
    Route::get('bs/do1', 'BootstrapCtrl@do1');
});



/**
 * API: Public Routes
 */
Route::group(array('namespace' => 'Bento\Ctrl'), function() {

    ## /menu routes
    Route::get('menu/{date}', 'MenuCtrl@show');
    
    ## /status routes
    Route::controller('status', 'StatusCtrl');

});



/**
 * API: Authenticated Users Only Routes
 */
Route::group(array('before' => 'api_auth', 'namespace' => 'Bento\Ctrl'), function() {
#Route::group(array('namespace' => 'Bento\Ctrl'), function() {

    ## /order routes
    Route::post('order/phase1', 'OrderCtrl@phase1');
    Route::post('order/phase2', 'OrderCtrl@phase2');

});



/**
 * Admins Only Routes
 * 
 * prefix => admin: All routes accessed through /admin/{...}
 * before => admin: Calling the admin filter on all routes.
 */
Route::group(array('prefix' => 'admin', 'before' => 'admin'), function() {
#Route::group(array('prefix' => 'admin'), function() {

    View::share('user', Session::get('adminUser'));
    
    // Admin index
    Route::get('/', function() {
        return View::make('admin.index');
    });
    
    Route::controller('user', 'Bento\Admin\Ctrl\UserCtrl');
    
    Route::controller('apitest', 'Bento\Admin\Ctrl\ApiTestCtrl');
    
}); // /End protected admin rotes

// These need to be able to be called without being logged in (duh)
Route::get('admin/login', function() {
    return View::make('admin.login');
});

Route::controller('admin', 'Bento\Admin\Ctrl\AdminUserCtrl');
/** /End Admin Routes */
