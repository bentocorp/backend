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

// Index
Route::get('/', function() {
    #return View::make('hello');
    return Redirect::away('http://signup.bentonow.com');
});

// AWS Health check
Route::get('/healthcheck', function() {
    return Response::make('ok', 200);
});

// Boostrapping (comment out when done)
#Route::group(array('namespace' => 'Bento\Ctrl'), function() {
#    Route::get('bs/do1', 'BootstrapCtrl@do1');
#});



/****************************************************************************
 * API: Public Routes
 ****************************************************************************
 */
Route::group(array('namespace' => 'Bento\Ctrl'), function() {

    ## /init routes
    Route::controller('init', 'InitCtrl');
    
    ## /menu routes
    Route::get('menu/{date}', 'MenuCtrl@show');
    
    ## /status routes
    Route::controller('status', 'StatusCtrl');
    
    ## PUBLIC /user routes
    Route::post('user/signup', 'UserCtrl@postSignup');
    Route::post('user/fbsignup', 'UserCtrl@postFbsignup');
    Route::post('user/login', 'UserCtrl@postLogin');
    Route::post('user/fblogin', 'UserCtrl@postFblogin');
    
    ## /misc routes
    Route::get('/ioscopy', 'MiscCtrl@getIoscopy');
    Route::get('/servicearea', 'MiscCtrl@getServicearea');
});



/****************************************************************************
 * API: Authenticated Users Only Routes
 ****************************************************************************
 */
Route::group(array('before' => 'api_auth', 'namespace' => 'Bento\Ctrl'), function() {
#Route::group(array('namespace' => 'Bento\Ctrl'), function() {
        
    ## /order routes
    Route::post('order/phase1', 'OrderCtrl@phase1');
    Route::post('order/phase2', 'OrderCtrl@phase2');
    
    ## /user auth routes
    Route::get('user/logout', 'UserCtrl@getLogout');
    
    ## /coupon routes
    Route::controller('coupon', 'CouponCtrl');
});



/****************************************************************************
 * Admins Only Routes
 ****************************************************************************
 * 
 * prefix => admin: All routes accessed through /admin/{...}
 * before => admin: Calling the admin filter on all routes.
 */
Route::group(array('prefix' => 'admin', 'before' => 'admin'), function() {
#Route::group(array('prefix' => 'admin'), function() {

    View::share('user', Session::get('adminUser'));
    
    // Admin index
    Route::get('/', function() {
        return Redirect::to('admin/dashboard');
    });
    
    Route::controller('dashboard', 'Bento\Admin\Ctrl\DashboardCtrl');
    
    Route::controller('pendingorder', 'Bento\Admin\Ctrl\PendingOrderCtrl');
    
    Route::controller('user', 'Bento\Admin\Ctrl\UserCtrl');
    
    Route::controller('apitest', 'Bento\Admin\Ctrl\ApiTestCtrl');
    
    Route::controller('misc', 'Bento\Admin\Ctrl\MiscCtrl');
    
    Route::controller('settings', 'Bento\Admin\Ctrl\SettingsCtrl');
    
}); // /End protected admin rotes

// These need to be able to be called without being logged in (duh)
Route::get('admin/login', function() {
    return View::make('admin.login');
});

Route::controller('admin', 'Bento\Admin\Ctrl\AdminUserCtrl');
/** /End Admin Routes */
